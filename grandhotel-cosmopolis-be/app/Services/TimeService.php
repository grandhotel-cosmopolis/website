<?php

namespace App\Services;

use App\Services\Interfaces\ITimeService;
use Carbon\Carbon;
use Exception;

class TimeService implements ITimeService
{

    public function validateTimeRange(Carbon $start, Carbon $end): bool
    {
        return $start <= $end;
    }

    public function isTime(mixed $obj): bool
    {
        try {
            Carbon::parse($obj);
        } catch (Exception) {
            return false;
        }
        return true;
    }
}
