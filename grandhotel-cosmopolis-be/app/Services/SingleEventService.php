<?php

namespace App\Services;

use App\Exceptions\InvalidTimeRangeException;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
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
        protected ITimeService $timeService
    ) {}

    /**
     * @throws InvalidTimeRangeException
     */
    public function createSingleEvent(
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $start,
        Carbon $end,
        string $eventLocationGuid,
        string $fileUploadGuid
    ) : SingleEvent {
        if (FileUpload::query()->where('guid', $fileUploadGuid)->count() != 1
            || EventLocation::query()->where('guid', $eventLocationGuid)->count() != 1)
        {
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
            $eventLocationGuid,
            $fileUploadGuid
        );
    }

    /**
     * @throws InvalidTimeRangeException
     */
    public function updateSingleEvent(
        string $eventGuid,
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $start,
        Carbon $end,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): SingleEvent {
        if (FileUpload::query()->where('guid', $fileUploadGuid)->count() != 1
            || EventLocation::query()->where('guid', $eventLocationGuid)->count() != 1)
        {
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
            $eventLocationGuid,
            $fileUploadGuid
        );
    }

    public function deleteSingleEvent(string $eventGuid): void
    {
        $this->eventRepository->deleteSingleEvent($eventGuid);
    }

    public function publishSingleEvent(string $eventGuid): SingleEvent
    {
        return $this->eventRepository->publishSingleEvent($eventGuid);
    }

    public function unpublishSingleEvent(string $eventGuid): SingleEvent
    {
        return $this->eventRepository->unpublishSingleEvent($eventGuid);
    }

    /**
     * @return Collection<int, SingleEvent>
     */
    public function getSingleEvents(Carbon $start, Carbon $end): Collection
    {
        return $this->eventRepository->getSingleEvents($start, $end);
    }
}
