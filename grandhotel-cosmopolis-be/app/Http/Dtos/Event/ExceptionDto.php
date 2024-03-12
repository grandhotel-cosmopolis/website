<?php

namespace App\Http\Dtos\Event;

use App\Models\EventLocation;
use App\Models\SingleEventException;
use DateTime;
use OpenApi\Attributes as OA;

#[OA\Schema]
class ExceptionDto
{
    #[OA\Property]
    public ?DateTime $start;

    #[OA\Property]
    public ?DateTime $end;

    #[OA\Property(ref: EventLocationDto::class)]
    public ?EventLocationDto $eventLocation;

    #[OA\Property]
    public ?bool $cancelled;

    public function __construct(
        ?DateTime         $start,
        ?DateTime         $end,
        ?EventLocationDto $eventLocation,
        ?bool             $cancelled
    )
    {
        $this->start = $start;
        $this->end = $end;
        $this->eventLocation = $eventLocation;
        $this->cancelled = $cancelled;
    }

    public static function create(
        SingleEventException $singleEventException,
        ?EventLocation       $eventLocation = null
    ): ExceptionDto
    {
        if (is_null($eventLocation)) {
            $eventLocation = $singleEventException->eventLocation()->first();
        }
        return new ExceptionDto(
            $singleEventException->start,
            $singleEventException->end,
            $eventLocation ? EventLocationDto::create($eventLocation) : null,
            $singleEventException->cancelled
        );
    }

}
