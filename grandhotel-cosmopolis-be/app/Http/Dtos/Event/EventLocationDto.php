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

    public function __construct(string $name, ?string $street, ?string $city) {
        $this->name = $name;
        $this->street = $street;
        $this->city = $city;
    }

    public static function create(EventLocation $eventLocation): EventLocationDto {
        return new EventLocationDto(
            $eventLocation->name,
            $eventLocation->street,
            $eventLocation->city
        );
    }
}
