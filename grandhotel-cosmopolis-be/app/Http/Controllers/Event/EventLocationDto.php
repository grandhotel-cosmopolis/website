<?php

namespace App\Http\Controllers\Event;

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
}
