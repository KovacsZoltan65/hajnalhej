<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('report:ceo-executive')
    ->dailyAt((string) config('analytics.executive_report.send_time', '07:30'))
    ->timezone((string) config('analytics.executive_report.timezone', 'Europe/Budapest'))
    ->withoutOverlapping();
