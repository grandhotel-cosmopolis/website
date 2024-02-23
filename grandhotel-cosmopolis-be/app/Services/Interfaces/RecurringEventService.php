<?php

namespace App\Services\Interfaces;

use App\Exceptions\InvalidTimeRangeException;
use App\Http\Controllers\Event\Recurrence;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\SingleEvent;
use App\Repositories\Interfaces\IRecurringEventRepository;
use App\Repositories\Interfaces\ISingleEventRepository;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RecurringEventService implements IRecurringEventService
{

    public function __construct(
        protected ITimeService              $timeService,
        protected IRecurringEventRepository $recurringEventRepository,
        protected ISingleEventRepository    $singleEventRepository
    )
    {
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function create(
        string     $titleDe,
        string     $titleEn,
        string     $descriptionDe,
        string     $descriptionEn,
        Carbon     $startFirstOccurrence,
        Carbon     $endFirstOccurrence,
        ?Carbon    $endRecurrence,
        Recurrence $recurrence,
        string     $recurrenceMetadata,
        string     $eventLocationGuid,
        string     $fileUploadGuid
    ): RecurringEvent
    {
        if (!$this->timeService->validateTimeRange($startFirstOccurrence, $endFirstOccurrence)
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

    /**
     * @throws InvalidTimeRangeException
     */
    public function update(
        string     $eventGuid,
        string     $titleDe,
        string     $titleEn,
        string     $descriptionDe,
        string     $descriptionEn,
        Carbon     $startFirstOccurrence,
        Carbon     $endFirstOccurrence,
        ?Carbon    $endRecurrence,
        Recurrence $recurrence,
        int        $recurrenceMetadata,
        string     $eventLocationGuid,
        string     $fileUploadGuid
    ): RecurringEvent
    {
        if (FileUpload::query()->where('guid', $fileUploadGuid)->count() != 1
            || EventLocation::query()->where('guid', $eventLocationGuid)->count() != 1) {
            throw new NotFoundHttpException();
        }

        if (!$this->timeService->validateTimeRange($startFirstOccurrence, $endFirstOccurrence)
            || (!is_null($endRecurrence) && !$this->timeService->validateTimeRange($startFirstOccurrence, $endRecurrence))) {
            throw new InvalidTimeRangeException();
        }

        $updatedEvent = $this->recurringEventRepository->update(
            $eventGuid,
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

        $this->updateSingleEvents($updatedEvent);

        return $updatedEvent;
    }

    public function delete(string $eventGuid): void
    {
        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('guid', $eventGuid)->first();
        if (is_null($recurringEvent)) {
            throw new NotFoundHttpException();
        }

        $singleEvents = $recurringEvent->singleEvents()->get();

        /** @var SingleEvent $singleEvent */
        foreach($singleEvents as $singleEvent) {
            $this->singleEventRepository->deleteSingleEvent($singleEvent->guid);
        }

        $this->recurringEventRepository->delete($recurringEvent->guid);
    }

    public function publish(string $eventGuid): RecurringEvent
    {
        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('guid', $eventGuid)->first();
        if (is_null($recurringEvent)) {
            throw new NotFoundHttpException();
        }

        $singleEvents = $recurringEvent->singleEvents()->get();

        /** @var SingleEvent $singleEvent */
        foreach($singleEvents as $singleEvent) {
            $this->singleEventRepository->publishSingleEvent($singleEvent->guid);
        }

        return $this->recurringEventRepository->publish($recurringEvent->guid);
    }

    public function unpublish(string $eventGuid): RecurringEvent
    {
        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('guid', $eventGuid)->first();
        if (is_null($recurringEvent)) {
            throw new NotFoundHttpException();
        }

        $singleEvents = $recurringEvent->singleEvents()->get();

        /** @var SingleEvent $singleEvent */
        foreach($singleEvents as $singleEvent) {
            $this->singleEventRepository->unpublishSingleEvent($singleEvent->guid);
        }

        return $this->recurringEventRepository->unpublish($recurringEvent->guid);
    }

    private function updateSingleEvents(
        RecurringEvent $recurringEvent
    ): void
    {
        /** @var EventLocation $eventLocation */
        $eventLocation = $recurringEvent->eventLocation()->first();

        /** @var FileUpload $fileUpload */
        $fileUpload = $recurringEvent->fileUpload()->first();

        $recurringEvent->singleEvents()->delete();

        $this->generateSingleEvents(
            $recurringEvent->start_first_occurrence,
            $recurringEvent->end_first_occurrence,
            $recurringEvent->end_recurrence,
            $recurringEvent,
            $eventLocation,
            $fileUpload
        );
    }

    private function generateSingleEvents(
        Carbon         $startFirstOccurrence,
        Carbon         $endFirstOccurrence,
        Carbon|null    $endRecurrence,
        RecurringEvent $recurringEvent,
        EventLocation  $eventLocation,
        FileUpload     $fileUpload,
    ): void
    {
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
                false,
                $eventLocation->guid,
                $fileUpload->guid
            );
            $singleEvent->is_recurring = true;
            $singleEvent->recurringEvent()->associate($recurringEvent);
            $singleEvent->save();

            [$currentStart, $currentEnd] = match ($recurringEvent->recurrence) {
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
