<?php

namespace App\Http\Controllers\Event;
use DateTime;
use OpenApi\Attributes as OA;

#[OA\Schema]
class CreateSingleEventRequestDto
{
    #[OA\Property]
    public string $title_de;

    #[OA\Property]
    public string $title_en;

    #[OA\Property]
    public string $description_de;

    #[OA\Property]
    public string $description_en;

    #[OA\Property(ref: EventLocationDto::class)]
    public EventLocationDto $eventLocation;

    #[OA\Property]
    public DateTime $start;

    #[OA\Property]
    public DateTime $end;

    #[OA\Property]
    public ?string $image_url;

    public function __construct(
        string $title_de,
        string $title_en,
        string $description_de,
        string $description_en,
        EventLocationDto $eventLocation,
        DateTime $start,
        DateTime $end,
        ?string $image_url
    ) {
        $this->title_de = $title_de;
        $this->title_en = $title_en;
        $this->description_de = $description_de;
        $this->description_en = $description_en;
        $this->eventLocation = $eventLocation;
        $this->start = $start;
        $this->end = $end;
        $this->image_url = $image_url;
    }
}
