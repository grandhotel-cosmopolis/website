<?php

namespace App\Repositories\Interfaces;

use App\Models\SingleEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface ISingleEventRepository
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
    ): SingleEvent;

    public function updateSingleEvent(
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
    );

    public function deleteSingleEvent(string $eventGuid): void;

    public function publishSingleEvent(string $eventGuid): SingleEvent;

    public function unpublishSingleEvent(string $eventGuid): SingleEvent;

    /** @return Collection<int, SingleEvent> */
    public function getSingleEvents(Carbon $start, Carbon $end): Collection;

    /** @return Collection<int, SingleEvent> */
    public function listAll(): Collection;
}
