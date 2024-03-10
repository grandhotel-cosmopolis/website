<?php

namespace App\Services;

use App\Exceptions\InvalidTimeRangeException;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\SingleEventException;
use App\Repositories\Interfaces\ISingleEventRepository;
use App\Services\Interfaces\ISingleEventService;
use App\Services\Interfaces\ITimeService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SingleEventService implements ISingleEventService
{
    public function __construct(
        protected ISingleEventRepository $eventRepository,
        protected ITimeService           $timeService
    )
    {
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function create(
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $start,
        Carbon $end,
        ?bool  $isPublic,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): SingleEvent
    {
        if (FileUpload::query()->where('guid', $fileUploadGuid)->count() != 1
            || EventLocation::query()->where('guid', $eventLocationGuid)->count() != 1) {
            throw new NotFoundHttpException();
        }

        if (!$this->timeService->validateTimeRange($start, $end)) {
            throw new InvalidTimeRangeException();
        }

        return $this->eventRepository->createSingleEvent(
            $titleDe,
            $titleEn,
            $descriptionDe,
            $descriptionEn,
            $start,
            $end,
            $isPublic,
            $eventLocationGuid,
            $fileUploadGuid
        );
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function update(
        string $eventGuid,
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $start,
        Carbon $end,
        ?bool  $isPublic,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): SingleEvent
    {
        if (FileUpload::query()->where('guid', $fileUploadGuid)->count() != 1
            || EventLocation::query()->where('guid', $eventLocationGuid)->count() != 1) {
            throw new NotFoundHttpException();
        }

        if (!$this->timeService->validateTimeRange($start, $end)) {
            throw new InvalidTimeRangeException();
        }

        return $this->eventRepository->updateSingleEvent(
            $eventGuid,
            $titleDe,
            $titleEn,
            $descriptionDe,
            $descriptionEn,
            $start,
            $end,
            $isPublic,
            $eventLocationGuid,
            $fileUploadGuid
        );
    }

    public function delete(string $eventGuid): void
    {
        $this->eventRepository->deleteSingleEvent($eventGuid);
    }

    public function publish(string $eventGuid): SingleEvent
    {
        return $this->eventRepository->publishSingleEvent($eventGuid);
    }

    public function unpublish(string $eventGuid): SingleEvent
    {
        return $this->eventRepository->unpublishSingleEvent($eventGuid);
    }

    /**
     * @return Collection<int, SingleEvent>
     */
    public function list(Carbon $start, Carbon $end): Collection
    {
        return $this->eventRepository->getSingleEvents($start, $end);
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function createOrUpdateEventException(
        string  $eventGuid,
        ?string $titleDe,
        ?string $titleEn,
        ?string $descriptionDe,
        ?string $descriptionEn,
        ?Carbon $start,
        ?Carbon $end,
        ?string $eventLocationGuid,
        ?string $fileUploadGuid,
    ): SingleEvent
    {
        /** @var SingleEvent $singleEvent */
        $singleEvent = SingleEvent::query()->where('guid', $eventGuid)->first();
        if (is_null($singleEvent)) {
            throw new NotFoundHttpException();
        }

        if(!$this->timeService->validateTimeRange($start ?? $singleEvent->start, $end ?? $singleEvent->end)){
            throw new InvalidTimeRangeException();
        }

        /** @var SingleEventException $existingException */
        $exception = $singleEvent->exception()->first();

        if (is_null($exception)) {
            $exception = new SingleEventException;
            $exception->singleEvent()->associate($singleEvent);
        }

        $exception->title_de = $titleDe;
        $exception->title_en = $titleEn;
        $exception->description_de = $descriptionDe;
        $exception->description_en = $descriptionEn;
        $exception->start = $start;
        $exception->end = $end;

        if (!is_null($eventLocationGuid)) {
            /** @var EventLocation $eventLocation */
            $eventLocation = EventLocation::query()->where('guid', $eventLocationGuid)->first();

            if(is_null($eventLocation)) {
                throw new NotFoundHttpException();
            }
            $exception->eventLocation()->associate($eventLocation);
        }
        if (!is_null($fileUploadGuid)) {
            /** @var FileUpload $fileUpload */
            $fileUpload = FileUpload::query()->where('guid', $fileUploadGuid)->first();

            if (is_null($fileUpload)) {
                throw new NotFoundHttpException();
            }
            $exception->fileUpload()->associate($fileUpload);
        }
        $exception->save();

        return $singleEvent;
    }

    public function cancelEvent(string $eventGuid): SingleEvent
    {
        return $this::setCancelledValue($eventGuid, true);
    }

    public function uncancelEvent(string $eventGuid): SingleEvent
    {
        return $this::setCancelledValue($eventGuid, false);
    }

    private static function setCancelledValue(string $guid, bool $cancelled): SingleEvent {
        /** @var SingleEvent $singleEvent */
        $singleEvent = SingleEvent::query()->where('guid', $guid)->first();
        if (is_null($singleEvent)) {
            throw new NotFoundHttpException();
        }

        /** @var SingleEventException $existingException */
        $exception = $singleEvent->exception()->first();

        if (is_null($exception) && !$cancelled) {
            // Do not create an exception if there isn't an exception already and uncancel is called
            return $singleEvent;
        }

        if (is_null($exception)) {
            $exception = new SingleEventException;
            $exception->singleEvent()->associate($singleEvent);
        }

        $exception->cancelled = $cancelled;
        $exception->save();

        return $singleEvent;
    }
}
