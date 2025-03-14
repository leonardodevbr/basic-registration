<?php

use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\ExpireBenefitDeliveriesCommand;

app(Schedule::class)->command(ExpireBenefitDeliveriesCommand::class)->everyFifteenMinutes();
