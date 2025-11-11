<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\PropertyOwner;
use App\Models\QuestionAnswerUser;
use App\Models\Room;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateNewRoomCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create_new_room_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create_new_room_command is running';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = config('app.env') == 'production' ? 1440 : 240; // 48 hours
        $getStartEndTimeByMinutes = $this->getStartEndTimeByMinutes($minutes, 'Y-m-d H:i:s');
        $startDateTime = $getStartEndTimeByMinutes['startTime'];
        $endDateTime = $getStartEndTimeByMinutes['endTime'];

        Log::info("CreateNewRoomCommand started at $startDateTime and ended at $endDateTime");
        $type = 'new_property';
        User::with('suburbsdata')
            ->whereRole(2)
            ->where('is_subscribed', '1')
            ->chunk(100, function ($users) use ($startDateTime, $endDateTime, $type) {
                // Collect all user IDs
                $userIds = $users->pluck('id')->toArray();

                // Fetch liked rooms for all users in this batch
                $likedRooms = DB::table(config('tables.tenant_likes'))
                    ->whereIn('tenant_id', $userIds)
                    ->select('tenant_id', 'room_id')
                    ->get()
                    ->groupBy('tenant_id');

                foreach ($users as $user) {
                    $ownerPropertyIds = PropertyOwner::whereUserId($user->id)?->first()?->properties()->pluck('id')->toArray() ?? [];

                    try {
                        $suburbNames = $user?->suburbsdata->pluck('name')
                            ->map(fn($name) => strtolower($name))
                            ->filter()
                            ->all();

                        if (empty($suburbNames)) {
                            continue;
                        }

                        // Directly find new rooms in suburbs (avoid extra property pluck)
                        $query = Room::query()
                            ->where('status', 1)
                            ->whereNull('deleted_at')
                            ->whereBetween('created_at', [$startDateTime, $endDateTime])
                            ->whereIn('property_id', function ($q) use ($suburbNames) {
                                $q->select('property_id')
                                    ->from('question_answers_user')
                                    ->where('question_answers_user.question_id', 57)
                                    ->where(function ($inner) use ($suburbNames) {
                                        foreach ($suburbNames as $name) {
                                            $inner->orWhere('answer', 'LIKE', "%{$name}%");
                                        }
                                    });
                            });

                        if (!empty($ownerPropertyIds)) {
                            $query->whereNotIn('property_id', $ownerPropertyIds);
                        }

                        // Exclude liked rooms in bulk
                        if (isset($likedRooms[$user->id])) {
                            $query->whereNotIn('id', $likedRooms[$user->id]->pluck('room_id'));
                        }

                        $roomIds   = $query->pluck('id')->toArray();
                        $roomCount = count($roomIds);

                        if ($roomCount === 0) {
                            continue;
                        }

                        // Check notification in one fast query
                        $alreadySent = Notification::where('user_id', $user->id)
                            ->where('type', $type)
                            ->whereBetween('created_at', [$startDateTime, $endDateTime])
                            ->exists();

                        if (!$alreadySent) {
                            // $message = "We found $roomCount new rooms that you may like!";
                            $message = "🏡  New rooms just dropped in your area. Take a look!";
                            Log::info("Sending notification to user {$user->id}: $message");

                            app(NotificationService::class)->create(
                                $user->id,
                                $type,
                                $user->name,
                                $message,
                                null,
                                ['roomIds' => (string) json_encode($roomIds)],
                                null
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error("Error in CreateNewRoomCommand for user {$user->id}: " . $e->getMessage() . ' - ' . $e->getLine());
                    }
                }
            });
    }

    public function getStartEndTimeByMinutes(int $minutes, string $formate = 'Y-m-d H:i:s'): array
    {
        $start = now()->subMinutes($minutes);
        $end = now();

        return [
            'startTime' => $start->format($formate),
            'endTime'   => $end->format($formate),
        ];
    }
}
