<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class WeeklyChatCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:weekly-chat-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $weeklyChats = config('constants.weekly_chat_count');
        $addOneWeek = config('app.env') == 'production' ? now()->addWeek()->startOfHour() : now()->addHours(1);

        User::whereDoesntHave('subscription', function ($q) {
            $q->where('status', '1')
                ->where('expires_at', '>', now()->startOfHour());
        })
            ->where(function ($q) {
                $q->whereDate('week_start_date', today())
                    ->orWhere(function ($sub) {
                        $sub->whereNull('week_start_date')
                            ->whereDate('created_at', today());
                    });
            })
            ->update([
                'chat_count' => $weeklyChats,
                'week_start_date' => $addOneWeek,
            ]);
    }
}
