<?php

use App\Console\Commands\CheckUserSubscribtion;
use App\Console\Commands\CreateNewRoomCommand;
use App\Console\Commands\SubscriptionExpiredNotification;
use App\Console\Commands\WeeklyChatCount;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\DeleteOldNotifications;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(DeleteOldNotifications::class)->daily();

Schedule::command(CreateNewRoomCommand::class)->daily();

Schedule::command(WeeklyChatCount::class)->weekends();

Schedule::command(CheckUserSubscribtion::class)->hourly();

Schedule::command(SubscriptionExpiredNotification::class)->hourly();