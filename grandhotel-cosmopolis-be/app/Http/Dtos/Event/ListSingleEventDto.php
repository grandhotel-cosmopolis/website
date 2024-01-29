<?php

namespace App\Http\Dtos\Event;
use OpenApi\Attributes as OA;

#[OA\Schema]
class ListSingleEventDto
{
    #[OA\Property(items: new OA\Items(ref: SingleEventDto::class))]
    /** @var $events SingleEventDto[] */
    public array $events;

    /** @param SingleEventDto[] $events */
    public function __construct(array $events) {
        $this->events = $events;
    }
}
