<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Models\PropertyOwner;

class CheckUserSubscribtion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-user-subscribtion';

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
        $users = User::whereDoesntHave('subscription', function ($q) {
            $q->where('status', '1')
                ->where('expires_at', '>', now());
        })->get();

        foreach ($users as $user) {
            if ($user->is_subscribed == 1) {
                $user->update(['is_subscribed' => 0]);
            }

            $properties = PropertyOwner::whereUserId($user->id)
                ->with(['properties.rooms'])
                ->get();

            foreach ($properties as $owner) {
                foreach ($owner->properties as $property) {
                    $property->update(['status' => 0]);
                    $property->rooms()->update(['status' => 0]);
                }
            }
        }
    }
}
