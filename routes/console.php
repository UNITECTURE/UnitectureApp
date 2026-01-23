<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('leaves:accrue')->monthly();

// 7 AM: Update yesterday's attendance (Finalize for employees)
Schedule::command('attendance:process yesterday')->dailyAt('07:00');

// 10 AM: Update today's attendance (For supervisors to check status)
Schedule::command('attendance:process today')->dailyAt('10:00');
