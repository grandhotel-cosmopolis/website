<?php

namespace App\Services\Interfaces;

use App\Http\Controllers\Event\Recurrence;
use App\Models\RecurringEvent;
use Carbon\Carbon;

interface IRecurringEventService
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
        string $recurrenceMetadata,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): RecurringEvent;
}
