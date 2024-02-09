<?php

namespace App\Repositories\Interfaces;

use App\Models\EventLocation;
use Illuminate\Support\Collection;

interface IEventLocationRepository
{
    public function create(
        string $name,
        ?string $street,
        ?string $city
    ): EventLocation;

    public function update(
        string $eventLocationGuid,
        string $name,
        ?string $street,
        ?string $city
    ): EventLocation;

    public function delete(string $eventLocationGuid): void;

    /** @return Collection<int, EventLocation> */
    public function list(): Collection;
}
