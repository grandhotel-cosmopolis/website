<?php

namespace App\Repositories;

use App\Http\Controllers\Event\Recurrence;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\User;
use App\Repositories\Interfaces\IRecurringEventRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RecurringEventRepository implements IRecurringEventRepository
{
    public function create(
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
        $newEvent = new RecurringEvent([
            'title_de' => $titleDe,
            'title_en' => $titleEn,
            'description_de' => $descriptionDe,
            'description_en' => $descriptionEn,
            'guid' => uuid_create(),
            'start_first_occurrence' => $startFirstOccurrence,
            'end_first_occurrence' => $endFirstOccurrence,
            'end_recurrence' => $endRecurrence,
            'recurrence' => $recurrence,
            'recurrence_metadata' => $recurrenceMetadata
        ]);

        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::query()
            ->where('guid', $eventLocationGuid)
            ->first();

        /** @var FileUpload $fileUpload */
        $fileUpload = FileUpload::query()
            ->where('guid', $fileUploadGuid)
            ->first();

        /** @var User $user */
        $user = Auth::user();

        if (is_null($eventLocation) || is_null($fileUpload) || is_null($user)) {
            throw new NotFoundHttpException();
        }

        $newEvent->fileUpload()->associate($fileUpload);
        $newEvent->eventLocation()->associate($eventLocation);
        $newEvent->createdBy()->associate($user);
        $newEvent->save();

        return $newEvent;
    }

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
        /** @var RecurringEvent $event */
        $event = RecurringEvent::query()
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
        $event->start_first_occurrence = $startFirstOccurrence;
        $event->end_first_occurrence = $endFirstOccurrence;
        $event->end_recurrence = $endRecurrence;
        $event->recurrence = $recurrence;
        $event->recurrence_metadata = $recurrenceMetadata;

        $event->eventLocation()->associate($eventLocation);
        $event->fileUpload()->associate($fileUpload);
        $event->save();
        return $event;
    }

    public function delete(string $guid): void
    {
        $deleted = RecurringEvent::query()
            ->where('guid', $guid)
            ->delete();
        if ($deleted != 1) {
            throw new NotFoundHttpException();
        }
    }

    public function publish(string $guid): RecurringEvent
    {
        /** @var RecurringEvent $event */
        $event = RecurringEvent::query()->where('guid', $guid)->first();

        if (is_null($event)) {
            throw  new NotFoundHttpException();
        }
        $event->is_public = true;
        $event->save();
        return $event;
    }

    public function unpublish(string $guid): RecurringEvent
    {
        /** @var RecurringEvent $event */
        $event = RecurringEvent::query()->where('guid', $guid)->first();

        if (is_null($event)) {
            throw  new NotFoundHttpException();
        }
        $event->is_public = false;
        $event->save();
        return $event;
    }
}
