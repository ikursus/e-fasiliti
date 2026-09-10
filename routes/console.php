<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * M10 background tasks (SDD §5.3). Both commands are idempotent: the
 * auto-close only touches tickets still waiting for confirmation, and the
 * SLA sweep records when each alert was already sent (NFR-A07).
 */
Schedule::command('tiket:amaran-sla')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('tiket:tutup-automatik')
    ->dailyAt('07:30')
    ->withoutOverlapping();
