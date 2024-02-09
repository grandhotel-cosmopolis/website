<?php

namespace App\Services\Interfaces;

use Carbon\Carbon;

interface ITimeService
{
    public function validateTimeRange(Carbon $start, Carbon $end): bool;

    public function isTime(mixed $obj): bool;

    /** @return Carbon[] */
    public function updateTimesForEveryXDays(Carbon $startTime, Carbon $endTime, int $numberOfDays): array;

    /** @return Carbon[] */
    public function updateTimesForEveryMonthAtDayX(Carbon $startTime, Carbon $endTime, int $dayOfMonth): array;

    /** @return int[] */
    public function getTime(Carbon $time): array;

    /** @return Carbon[] */
    public function updateTimesForEveyLastDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array;

    /** @return Carbon[] */
    public function updateTimesForEveryFirstDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array;

    /** @return Carbon[] */
    public function updateTimesForEveryThirdDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array;

    /** @return Carbon[] */
    public function updateTimesForEverySecondDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array;
}
