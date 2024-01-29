<?php

namespace App\Http\Controllers\Event;
use OpenApi\Attributes as OA;

#[OA\Schema]
enum Recurrence: string
{
    case EVERY_X_DAYS = 'every_x_days';
    case EVERY_MONTH_AT_DAY_X = 'every_month_at_day_x';
    case EVERY_LAST_DAY_IN_MONTH = 'every_last_day_in_month';
    case EVERY_FIRST_DAY_IN_MONTH = 'every_first_day_in_month';
    case EVERY_SECOND_DAY_IN_MONTH = 'every_second_first_day_in_month';
    case EVERY_THIRD_DAY_IN_MONTH = 'every_third_day_in_month';
}
