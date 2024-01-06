<?php

namespace App\Http\Controllers\Event;
use OpenApi\Attributes as OA;

#[OA\Schema]
class ListSingleEventDto
{
    #[OA\Property(items: new OA\Items(ref: SingleEventDto::class))]
    /** @var $events SingleEventDto[] */
    public array $events;

    public function __construct(array $events) {
        $this->events = $events;
    }
}
