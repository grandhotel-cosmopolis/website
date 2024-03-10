<?php

namespace App\Http\Dtos\Event;

use App\Http\Dtos\File\FileDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEventException;
use DateTime;
use OpenApi\Attributes as OA;

#[OA\Schema]
class ExceptionDto
{
    #[OA\Property]
    public ?string $titleDe;

    #[OA\Property]
    public ?string $titleEn;

    #[OA\Property]
    public ?string $descriptionDe;

    #[OA\Property]
    public ?string $descriptionEn;

    #[OA\Property]
    public ?DateTime $start;

    #[OA\Property]
    public ?DateTime $end;

    #[OA\Property(ref: EventLocationDto::class)]
    public ?EventLocationDto $eventLocation;

    #[OA\Property(ref: FileDto::class)]
    public ?FileDto $image;

    #[OA\Property]
    public ?bool $cancelled;

    public function __construct(
        ?string           $titleDe,
        ?string           $titleEn,
        ?string           $descriptionDe,
        ?string           $descriptionEn,
        ?DateTime         $start,
        ?DateTime         $end,
        ?EventLocationDto $eventLocation,
        ?FileDto          $image,
        ?bool             $cancelled
    )
    {
        $this->titleDe = $titleDe;
        $this->titleEn = $titleEn;
        $this->descriptionDe = $descriptionDe;
        $this->descriptionEn = $descriptionEn;
        $this->start = $start;
        $this->end = $end;
        $this->eventLocation = $eventLocation;
        $this->image = $image;
        $this->cancelled = $cancelled;
    }

    public static function create(
        SingleEventException $singleEventException,
        ?EventLocation       $eventLocation = null,
        ?FileUpload          $fileUpload = null
    ): ExceptionDto
    {
        if (is_null($eventLocation)) {
            $eventLocation = $singleEventException->eventLocation()->first();
        }
        if (is_null($fileUpload)) {
            $fileUpload = $singleEventException->fileUpload()->first();
        }
        return new ExceptionDto(
            $singleEventException->title_de,
            $singleEventException->title_en,
            $singleEventException->description_de,
            $singleEventException->description_en,
            $singleEventException->start,
            $singleEventException->end,
            $eventLocation ? EventLocationDto::create($eventLocation) : null,
            $fileUpload ? FileDto::create($fileUpload) : null,
            $singleEventException->cancelled
        );
    }

}
