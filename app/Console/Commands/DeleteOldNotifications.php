<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class DeleteOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to delete notifications that are older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = Notification::where('created_at', '<', now()->subDays(100))->delete();
        $this->info("Deleted $deleted notifications.");
        Log::info("Deleted $deleted old bookings.");
    }
}
