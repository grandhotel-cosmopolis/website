<?php

namespace App\Http\Dtos\Event;
use OpenApi\Attributes as OA;

#[OA\Schema]
class ListEventLocationDto
{
    #[OA\Property(items: new OA\Items(ref: EventLocationDto::class))]
    /** @var $evenervetLocations EventLocationDto[] */
    public array $eventLocations;

    /** @param EventLocationDto[] $eventLocations */
    public function __construct(array $eventLocations) {
        $this->eventLocations = $eventLocations;
    }
}
