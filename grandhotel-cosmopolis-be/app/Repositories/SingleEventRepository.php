<?php

namespace App\Repositories;

use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\SingleEvent;
use App\Repositories\Interfaces\ISingleEventRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SingleEventRepository implements ISingleEventRepository
{
    public function createSingleEvent(
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
        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::query()
            ->where('guid', $eventLocationGuid)
            ->first();

        $fileUpload = FileUpload::query()
            ->where('guid', $fileUploadGuid)
            ->first();

        if (is_null($eventLocation) || is_null($fileUpload)) {
            throw new NotFoundHttpException();
        }

        $event = new SingleEvent([
            'guid' => uuid_create(),
            'title_de' => $titleDe,
            'title_en' => $titleEn,
            'description_de' => $descriptionDe,
            'description_en' => $descriptionEn,
            'start' => $start,
            'end' => $end,
            'is_recurring' => false,
            'is_public' => $isPublic ?? false
        ]);
        $event->eventLocation()->associate($eventLocation);
        $event->fileUpload()->associate($fileUpload);
        $event->createdBy()->associate(Auth::user());
        $event->save();
        return $event;
    }

    public function updateSingleEvent(
        string $eventGuid,
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $start,
        Carbon $end,
        ?bool $isPublic,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): SingleEvent
    {
        /** @var SingleEvent $event */
        $event = SingleEvent::query()
            ->where('guid', $eventGuid)
            ->first();

        /** @noinspection DuplicatedCode */
        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::query()
            ->where('guid', $eventLocationGuid)
            ->first();

        /** @var FileUpload $fileUpload */
        $fileUpload = FileUpload::query()
            ->where('guid', $fileUploadGuid)
            ->first();

        if (is_null($event) || is_null($eventLocation) || is_null($fileUpload)) {
            throw new NotFoundHttpException();
        }

        $event->title_de = $titleDe;
        $event->title_en = $titleEn;
        $event->description_de = $descriptionDe;
        $event->description_en = $descriptionEn;
        $event->start = $start;
        $event->end = $end;
        $event->is_public = $isPublic ?? $event->is_public;

        $event->eventLocation()->associate($eventLocation);

        $event->fileUpload()->associate($fileUpload);
        $event->save();
        return $event;
    }

    public function deleteSingleEvent(string $eventGuid): void
    {
        /** @var SingleEvent $singleEvent */
        $singleEvent = SingleEvent::query()
            ->where('guid', $eventGuid)
            ->first();
        if(is_null($singleEvent)) {
            throw new NotFoundHttpException();
        }
        $singleEvent->exception()->delete();
        $singleEvent->delete();
    }

    public function publishSingleEvent(string $eventGuid): SingleEvent
    {
        /** @var SingleEvent $event */
        $event = SingleEvent::query()->where('guid', $eventGuid)->first();
        if (is_null($event)) {
            throw new NotFoundHttpException();
        }
        $event->is_public = true;
        $event->save();
        return $event;
    }

    public function unpublishSingleEvent(string $eventGuid): SingleEvent
    {
        /** @var SingleEvent $event */
        $event = SingleEvent::query()->where('guid', $eventGuid)->first();
        if (is_null($event)) {
            throw new NotFoundHttpException();
        }
        $event->is_public = false;
        $event->save();
        return $event;
    }

    /**
     * @return Collection<int, SingleEvent>
     */
    public function getSingleEvents(Carbon $start, Carbon $end): Collection
    {
        return SingleEvent::query()
            ->where(function (Builder $query) use ($start, $end) {
                $query
                    ->where('start', '>', $start)
                    ->where('end', '<', $end)
                    ->where('is_public', true);
            })
            ->orWhere(function (Builder $query) use ($start) {
                $query
                    ->where('start', '<', $start)
                    ->where('end', '>', $start)
                    ->where('is_public', true);
            })
            ->orWhere(function (Builder $query) use ($end) {
                $query
                    ->where('end', '>', $end)
                    ->where('start', '<', $end)
                    ->where('is_public', true);
            })
            ->orderBy('start')
            ->get();
    }

    public function listAll(): Collection
    {
        return SingleEvent::query()
            ->where('end', '>', Carbon::now())
            ->whereNull('recurring_event_id')
            ->get();
    }

    public function listAllByRecurringEventGuid(string $recurringEventGuid): Collection
    {
        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()
            ->where('guid', $recurringEventGuid)
            ->first();
        if (is_null($recurringEvent)) {
            throw new NotFoundHttpException();
        }
        return $recurringEvent->singleEvents()
            ->where('end', '>', Carbon::now())
            ->get();
    }
}
