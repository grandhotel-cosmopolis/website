<?php

namespace App\Http\Dtos\Event;

use App\Http\Dtos\File\FileDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\SingleEventException;
use DateTime;
use OpenApi\Attributes as OA;

#[OA\Schema]
class SingleEventDto
{
    #[OA\Property]
    public string $guid;

    #[OA\Property]
    public string $titleDe;

    #[OA\Property]
    public string $titleEn;

    #[OA\Property]
    public string $descriptionDe;

    #[OA\Property]
    public string $descriptionEn;

    #[OA\Property(ref: EventLocationDto::class)]
    public EventLocationDto $eventLocation;

    #[OA\Property]
    public DateTime $start;

    #[OA\Property]
    public DateTime $end;

    #[OA\Property(ref: FileDto::class)]
    public FileDto $image;

    #[OA\Property]
    public bool $isPublic;

    #[OA\Property(ref: ExceptionDto::class)]
    public ?ExceptionDto $exception;

    public function __construct(
        string           $guid,
        string           $titleDe,
        string           $titleEn,
        string           $descriptionDe,
        string           $descriptionEn,
        EventLocationDto $eventLocation,
        DateTime         $start,
        DateTime         $end,
        FileDto          $fileDto,
        bool             $isPublic,
        ?ExceptionDto    $exception,
    )
    {
        $this->guid = $guid;
        $this->titleDe = $titleDe;
        $this->titleEn = $titleEn;
        $this->descriptionDe = $descriptionDe;
        $this->descriptionEn = $descriptionEn;
        $this->eventLocation = $eventLocation;
        $this->start = $start;
        $this->end = $end;
        $this->image = $fileDto;
        $this->isPublic = $isPublic;
        $this->exception = $exception;
    }

    public static function create(
        SingleEvent           $singleEvent,
        ?EventLocation        $eventLocation = null,
        ?FileUpload           $fileUpload = null,
        ?SingleEventException $exception = null
    ): SingleEventDto
    {
        if (is_null($eventLocation)) {
            $eventLocation = $singleEvent->eventLocation()->first();
        }
        if (is_null($fileUpload)) {
            $fileUpload = $singleEvent->fileUpload()->first();
        }
        if(is_null($exception)) {
            $exception = $singleEvent->exception()->first();
        }
        return new SingleEventDto(
            $singleEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            EventLocationDto::create($eventLocation),
            $singleEvent->start,
            $singleEvent->end,
            FileDto::create($fileUpload),
            $singleEvent->is_public,
            $exception ? ExceptionDto::create($exception) : null
        );
    }
}
