<?php

namespace App\Services\Interfaces;

use Carbon\Carbon;

interface ITimeService
{
    public function validateTimeRange(Carbon $start, Carbon $end): bool;

    public function isTime(mixed $obj): bool;
}
