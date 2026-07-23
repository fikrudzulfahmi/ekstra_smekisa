<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Jadwalkan backup rutin setiap hari (ke format SQL dan upload ke Google Drive dengan auto-cleanup)
Schedule::command('backup:sql')->dailyAt('01:00');
