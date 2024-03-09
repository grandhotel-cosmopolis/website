<?php

namespace App\Http\Controllers\Event;
use OpenApi\Attributes as OA;

#[OA\Schema]
enum Recurrence: string
{
    case EVERY_X_DAYS = 'EVERY_X_DAYS';
    case EVERY_MONTH_AT_DAY_X = 'EVERY_MONTH_AT_DAY_X';
    case EVERY_LAST_DAY_IN_MONTH = 'EVERY_LAST_DAY_IN_MONTH';
    case EVERY_FIRST_DAY_IN_MONTH = 'EVERY_FIRST_DAY_IN_MONTH';
    case EVERY_SECOND_DAY_IN_MONTH = 'EVERY_SECOND_DAY_IN_MONTH';
    case EVERY_THIRD_DAY_IN_MONTH = 'EVERY_THIRD_DAY_IN_MONTH';
}
