<?php

namespace App\Repositories\Interfaces;

use App\Http\Controllers\Event\Recurrence;
use App\Models\RecurringEvent;
use Carbon\Carbon;

interface IRecurringEventRepository
{
    public function create(
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $startFirstOccurrence,
        Carbon $endFirstOccurrence,
        ?Carbon $endRecurrence,
        Recurrence $recurrence,
        int $recurrenceMetadata,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): RecurringEvent;

    public function update(
        string $eventGuid,
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $startFirstOccurrence,
        Carbon $endFirstOccurrence,
        ?Carbon $endRecurrence,
        Recurrence $recurrence,
        int $recurrenceMetadata,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): RecurringEvent;

    public function delete(string $guid): void;

    public function publish(string $guid): RecurringEvent;

    public function unpublish(string $guid): RecurringEvent;
}
