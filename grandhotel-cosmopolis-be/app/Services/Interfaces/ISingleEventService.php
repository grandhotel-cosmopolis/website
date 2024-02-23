<?php

namespace App\Services\Interfaces;

use App\Models\SingleEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface ISingleEventService
{
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
    ): SingleEvent;

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
    ): SingleEvent;

    public function delete(string $eventGuid): void;

    public function publish(string $eventGuid): SingleEvent;

    public function unpublish(string $eventGuid): SingleEvent;

    /**
     * @return Collection<int, SingleEvent>
     */
    public function list(Carbon $start, Carbon $end): Collection;
}
