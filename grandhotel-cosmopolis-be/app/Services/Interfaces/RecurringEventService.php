<?php

namespace App\Services\Interfaces;

use App\Exceptions\InvalidTimeRangeException;
use App\Http\Controllers\Event\Recurrence;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Repositories\Interfaces\IRecurringEventRepository;
use App\Repositories\Interfaces\ISingleEventRepository;
use Carbon\Carbon;

class RecurringEventService implements IRecurringEventService
{

    public function __construct(
        protected ITimeService $timeService,
        protected IRecurringEventRepository $recurringEventRepository,
        protected ISingleEventRepository $singleEventRepository
    ) {}

    /**
     * @throws InvalidTimeRangeException
     */
    public function create(
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $startFirstOccurrence,
        Carbon $endFirstOccurrence,
        ?Carbon $endRecurrence,
        Recurrence $recurrence,
        string $recurrenceMetadata,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): RecurringEvent
    {
        if(!$this->timeService->validateTimeRange($startFirstOccurrence, $endFirstOccurrence)
            || (!is_null($endRecurrence) && !$this->timeService->validateTimeRange($startFirstOccurrence, $endRecurrence))) {
            throw new InvalidTimeRangeException();
        }

        $createdEvent = $this->recurringEventRepository->create(
            $titleDe,
            $titleEn,
            $descriptionDe,
            $descriptionEn,
            $startFirstOccurrence,
            $endFirstOccurrence,
            $endRecurrence,
            $recurrence,
            $recurrenceMetadata,
            $eventLocationGuid,
            $fileUploadGuid
        );

        /** @var EventLocation $eventLocation */
        $eventLocation = $createdEvent->eventLocation()->first();

        /** @var FileUpload $fileUpload */
        $fileUpload = $createdEvent->fileUpload()->first();

        $this->generateSingleEvents(
            $startFirstOccurrence,
            $endFirstOccurrence,
            $endRecurrence,
            $createdEvent,
            $eventLocation,
            $fileUpload
        );

        return $createdEvent;
    }

    private function generateSingleEvents(
        Carbon $startFirstOccurrence,
        Carbon $endFirstOccurrence,
        Carbon | null $endRecurrence,
        RecurringEvent $recurringEvent,
        EventLocation $eventLocation,
        FileUpload $fileUpload,
    ): void {
        if (is_null($endRecurrence) || $endRecurrence > Carbon::now()->endOfYear()) {
            $endRecurrence = Carbon::now()->endOfYear();
        }
        $currentStart = $startFirstOccurrence;
        $currentEnd = $endFirstOccurrence;

        while ($currentStart < $endRecurrence) {
            // new single event
            $singleEvent = $this->singleEventRepository->createSingleEvent(
                $recurringEvent->title_de,
                $recurringEvent->title_en,
                $recurringEvent->description_de,
                $recurringEvent->description_en,
                $currentStart,
                $currentEnd,
                $eventLocation->guid,
                $fileUpload->guid
            );
            $singleEvent->is_recurring = true;
            $singleEvent->recurringEvent()->associate($recurringEvent);
            $singleEvent->save();

            [$currentStart, $currentEnd] = match($recurringEvent->recurrence) {
                Recurrence::EVERY_X_DAYS => $this->timeService->updateTimesForEveryXDays($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_MONTH_AT_DAY_X => $this->timeService->updateTimesForEveryMonthAtDayX($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_LAST_DAY_IN_MONTH => $this->timeService->updateTimesForEveyLastDayInMonth($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_FIRST_DAY_IN_MONTH => $this->timeService->updateTimesForEveryFirstDayInMonth($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_SECOND_DAY_IN_MONTH => $this->timeService->updateTimesForEverySecondDayInMonth($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_THIRD_DAY_IN_MONTH => $this->timeService->updateTimesForEveryThirdDayInMonth($currentStart, $currentEnd, $recurringEvent->recurrence_metadata)
            };
        }
    }
}
