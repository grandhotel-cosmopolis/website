<?php

namespace App\Http\Dtos\Event;
use App\Http\Dtos\File\FileDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use DateTime;
use OpenApi\Attributes as OA;

#[OA\Schema]
class SingleEventDto
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

    #[OA\Property(ref: FileDto::class)]
    public FileDto $image;

    public function __construct(
        string $title_de,
        string $title_en,
        string $description_de,
        string $description_en,
        EventLocationDto $eventLocation,
        DateTime $start,
        DateTime $end,
        FileDto $fileDto
    ) {
        $this->title_de = $title_de;
        $this->title_en = $title_en;
        $this->description_de = $description_de;
        $this->description_en = $description_en;
        $this->eventLocation = $eventLocation;
        $this->start = $start;
        $this->end = $end;
        $this->image = $fileDto;
    }

    public static function create(SingleEvent $singleEvent, ?EventLocation $eventLocation = null, ?FileUpload $fileUpload = null): SingleEventDto {
        if (is_null($eventLocation)) {
            /** @var EventLocation $eventLocation */
            $eventLocation = $singleEvent->eventLocation()->first();
        }
        if (is_null($fileUpload)) {
            /** @var FileUpload $fileUpload */
            $fileUpload = $singleEvent->fileUpload()->first();
        }
        return new SingleEventDto(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            EventLocationDto::create($eventLocation),
            $singleEvent->start,
            $singleEvent->end,
            FileDto::create($fileUpload)
        );
    }
}
