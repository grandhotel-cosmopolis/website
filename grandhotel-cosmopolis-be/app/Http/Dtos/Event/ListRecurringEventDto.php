<?php

namespace App\Http\Dtos\Event;
use OpenApi\Attributes as OA;

#[OA\Schema]
class ListRecurringEventDto
{
    #[OA\Property(items: new OA\Items(ref: RecurringEventDto::class))]
    /** @var $events RecurringEventDto[] */
    public array $events;

    /** @param RecurringEventDto[] $events */
    public function __construct(array $events) {
        $this->events = $events;
    }

}
