<?php

namespace App\Services;

use App\Services\Interfaces\ITimeService;
use Carbon\Carbon;
use Exception;

class TimeService implements ITimeService
{

    public function validateTimeRange(Carbon $start, Carbon $end): bool
    {
        return $start < $end;
    }

    public function isTime(mixed $obj): bool
    {
        if(is_null($obj)) {
            return false;
        }

        if(!($obj instanceof Carbon) && !is_string($obj)) {
            return false;
        }
        try {
            Carbon::parse($obj);
        } catch (Exception) {
            return false;
        }
        return true;
    }

    /** @return Carbon[] */
    public function updateTimesForEveryXDays(Carbon $startTime, Carbon $endTime, int $numberOfDays): array {
        return [$startTime->clone()->addDays($numberOfDays), $endTime->clone()->addDays($numberOfDays)];
    }

    /** @return Carbon[] */
    public function updateTimesForEveryMonthAtDayX(Carbon $startTime, Carbon $endTime, int $dayOfMonth): array {
        $startTimeClone = $startTime->clone();
        $endTimeClone = $endTime->clone();
        $clonedStart = $startTimeClone->clone();
        $clonedStart->endOfMonth()->addDay();
        if ($clonedStart->daysInMonth < $dayOfMonth) {
            $startTimeClone->setDay($clonedStart->daysInMonth)->setMonth($clonedStart->month);
        } else {
            $startTimeClone->addMonth();
            $startTimeClone->setDay($dayOfMonth);
        }

        $clonedEnd = $endTimeClone->clone();
        $clonedEnd->endOfMonth()->addDay();
        if($clonedEnd->daysInMonth < $dayOfMonth) {
            $endTimeClone->setDay($clonedStart->daysInMonth)->setMonth($clonedStart->month);
        } else {
            $endTimeClone->addMonth();
            $endTimeClone->setDay($dayOfMonth);
        }

        return [$startTimeClone, $endTimeClone];
    }

    /** @return int[] */
    public function getTime(Carbon $time): array {
        return [$time->hour, $time->minute, $time->second, $time->millisecond];
    }

    /** @return Carbon[] */
    public function updateTimesForEveyLastDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array {
        $startTimeCloned = $startTime->clone();
        $endTimeCloned = $endTime->clone();
        [$startHour, $startMinute, $startSecond, $startMillisecond]  = self::getTime($startTimeCloned);
        $startTimeCloned->endOfMonth()->addDay()->lastOfMonth($dayOfWeek)
            ->setHour($startHour)->setMinute($startMinute)->setSecond($startSecond)->setMillisecond($startMillisecond);
        [$endHour, $endMinute, $endSecond, $endMillisecond] = self::getTime($endTimeCloned);
        $endTimeCloned->endOfMonth()->addDay()->lastOfMonth($dayOfWeek)
            ->setHour($endHour)->setMinute($endMinute)->setSecond($endSecond)->setMillisecond($endMillisecond);
        return [$startTimeCloned, $endTimeCloned];
    }

    /** @return Carbon[] */
    public function updateTimesForEveryFirstDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array {
        $startTimeCloned = $startTime->clone();
        $endTimeCloned = $endTime->clone();
        [$startHour, $startMinute, $startSecond, $startMillisecond]  = self::getTime($startTimeCloned);
        $startTimeCloned->addMonth()->firstOfMonth($dayOfWeek)
            ->setHour($startHour)->setMinute($startMinute)->setSecond($startSecond)->setMillisecond($startMillisecond);
        [$endHour, $endMinute, $endSecond, $endMillisecond] = self::getTime($endTimeCloned);
        $endTimeCloned->addMonth()->firstOfMonth($dayOfWeek)
            ->setHour($endHour)->setMinute($endMinute)->setSecond($endSecond)->setMillisecond($endMillisecond);
        return [$startTimeCloned, $endTimeCloned];
    }

    /** @return Carbon[] */
    public function updateTimesForEveryThirdDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array {
        $startTimeCloned = $startTime->clone();
        $endTimeCloned = $endTime->clone();
        [$startHour, $startMinute, $startSecond, $startMillisecond]  = self::getTime($startTimeCloned);
        $startTimeCloned->addMonth()->nthOfMonth(3, $dayOfWeek)
            ->setHour($startHour)->setMinute($startMinute)->setSecond($startSecond)->setMillisecond($startMillisecond);
        [$endHour, $endMinute, $endSecond, $endMillisecond] = self::getTime($endTimeCloned);
        $endTimeCloned->addMonth()->nthOfMonth(3, $dayOfWeek)
            ->setHour($endHour)->setMinute($endMinute)->setSecond($endSecond)->setMillisecond($endMillisecond);
        return [$startTimeCloned, $endTimeCloned];
    }

    /** @return Carbon[] */
    public function updateTimesForEverySecondDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array {
        $startTimeCloned = $startTime->clone();
        $endTimeCloned = $endTime->clone();
        [$startHour, $startMinute, $startSecond, $startMillisecond]  = self::getTime($startTimeCloned);
        $startTimeCloned->addMonth()->nthOfMonth(2, $dayOfWeek)
            ->setHour($startHour)->setMinute($startMinute)->setSecond($startSecond)->setMillisecond($startMillisecond);
        [$endHour, $endMinute, $endSecond, $endMillisecond] = self::getTime($endTimeCloned);
        $endTimeCloned->addMonth()->nthOfMonth(2, $dayOfWeek)
            ->setHour($endHour)->setMinute($endMinute)->setSecond($endSecond)->setMillisecond($endMillisecond);
        return [$startTimeCloned, $endTimeCloned];
    }
}
