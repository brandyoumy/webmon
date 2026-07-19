<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$cronInterval = (int) (\Illuminate\Support\Facades\Schema::hasTable('settings')
    ? (Setting::get('cron_interval', 5))
    : 5);

$event = Schedule::command('monitor:websites')->withoutOverlapping(300);

match ($cronInterval) {
    1  => $event->everyMinute(),
    10 => $event->everyTenMinutes(),
    15 => $event->everyFifteenMinutes(),
    30 => $event->everyThirtyMinutes(),
    60 => $event->hourly(),
    default => $event->everyFiveMinutes(),
};
