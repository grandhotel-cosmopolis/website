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

}
