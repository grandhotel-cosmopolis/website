<?php

namespace App\Services\Interfaces;

use App\Models\SingleEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface IEventService
{
    public function createSingleEvent(
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Carbon $start,
        Carbon $end,
        string $eventLocationGuid,
        string $fileUploadGuid
    ): SingleEvent;

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
    ): SingleEvent;

    public function deleteSingleEvent(string $eventGuid): void;

    public function publishSingleEvent(string $eventGuid): SingleEvent;

    public function unpublishSingleEvent(string $eventGuid): SingleEvent;

    /**
     * @return Collection<int, SingleEvent>
     */
    public function getSingleEvents(Carbon $start, Carbon $end): Collection;
}
