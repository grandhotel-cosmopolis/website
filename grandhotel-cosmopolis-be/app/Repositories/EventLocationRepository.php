<?php

namespace App\Repositories;

use App\Models\EventLocation;
use App\Repositories\Interfaces\IEventLocationRepository;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class EventLocationRepository implements IEventLocationRepository
{
    public function create(string $name, ?string $street, ?string $city): EventLocation
    {
        $eventLocation = new EventLocation([
            'guid' => uuid_create(),
            'name' => $name,
            'street' => $street,
            'city' => $city
        ]);
        $eventLocation->save();
        return $eventLocation;
    }

    public function update(string $eventLocationGuid, string $name, ?string $street, ?string $city): EventLocation
    {
        /** @var EventLocation $location */
        $location = EventLocation::query()
            ->where('guid', $eventLocationGuid)
            ->first();
        if (is_null($location)) {
            throw new NotFoundHttpException();
        }

        $location->name = $name;
        $location->street = $street;
        $location->city = $city;
        $location->save();
        return $location;
    }

    public function delete(string $eventLocationGuid): void
    {
        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::query()
            ->where('guid', $eventLocationGuid)
            ->first();

        if (is_null($eventLocation)) {
            throw new NotFoundHttpException();
        }

        if($eventLocation->singleEvents()->get()->count() > 0) {
            throw new UnprocessableEntityHttpException();
        }

        $deleted = EventLocation::query()
            ->where('guid', $eventLocationGuid)
            ->delete();

        if($deleted != 1) {
            throw new NotFoundHttpException();
        }
    }

    public function list(): Collection
    {
        return EventLocation::all();
    }
}
