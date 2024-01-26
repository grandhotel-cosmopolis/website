<?php

namespace App\Http\Dtos\Event;

use App\Models\EventLocation;
use OpenApi\Attributes as OA;

#[OA\Schema]
class EventLocationDto
{
    #[OA\Property]
    public string $name;

    #[OA\Property]
    public ?string $street;

    #[OA\Property]
    public ?string $city;

    #[OA\Property]
    public string $guid;

    public function __construct(string $name, string $guid, ?string $street, ?string $city) {
        $this->name = $name;
        $this->guid = $guid;
        $this->street = $street;
        $this->city = $city;
    }

    public static function create(EventLocation $eventLocation): EventLocationDto {
        return new EventLocationDto(
            $eventLocation->name,
            $eventLocation->guid,
            $eventLocation->street,
            $eventLocation->city
        );
    }
}
