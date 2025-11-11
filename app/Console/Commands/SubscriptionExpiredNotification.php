<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Console\Command;

class SubscriptionExpiredNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:subscription-expired-notification';

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
        $now = now();
        $next24Hours = config('app.env') == 'production' ? now()->addDay() : now()->addHour();
        $last24Hours = config('app.env') == 'production' ? now()->subDay() : now()->subHour();
        
        $users = User::whereHas('subscription', function ($q) use ($now, $next24Hours) {
            $q->where('status', '1')
                ->whereBetween('expires_at', [$now, $next24Hours]);
        })
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users with expiring subscriptions found.');
            return 0;
        }

        foreach ($users as $user) {
            $alreadySent = Notification::where('user_id', $user->id)
                ->where('type', 'subscription_expiring')
                ->where('created_at', '>=', $last24Hours) // last 24h
                ->exists();

            if (!$alreadySent) {
                app(NotificationService::class)->create(
                    $user->id,
                    'subscription_expiring',
                    "Plan Expiring Soon!",
                    "⏰ Tick-tock… your plan expires in 24 hours. Don’t forget to upgrade again tomorrow to keep all your features unlocked.",
                    null,
                    ['roomIds' => json_encode([])],
                    null
                );

                $this->info("Notification sent to user: {$user->id} ({$user->name})");
            }
        }
    }
}
