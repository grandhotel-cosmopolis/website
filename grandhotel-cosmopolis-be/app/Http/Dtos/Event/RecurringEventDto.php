<?php

namespace App\Http\Dtos\Event;
use App\Http\Controllers\Event\Recurrence;
use App\Http\Dtos\File\FileDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use DateTime;
use OpenApi\Attributes as OA;

#[OA\Schema]
class RecurringEventDto
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

    #[OA\Property]
    public bool $isPublic;

    #[OA\Property(ref: EventLocationDto::class)]
    public EventLocationDto $eventLocation;

    #[OA\Property]
    public DateTime $startFirstOccurrence;

    #[OA\Property]
    public DateTime $endFirstOccurrence;

    #[OA\Property]
    public DateTime | null $endRecurrence;

    #[OA\Property(ref: FileDto::class)]
    public FileDto $image;

    #[OA\Property(ref: Recurrence::class)]
    public Recurrence $recurrence;

    #[OA\Property]
    public int $recurrenceMetadata;

    public function __construct(
        string $guid,
        string $titleDe,
        string $titleEn,
        string $descriptionDe,
        string $descriptionEn,
        Recurrence $recurrence,
        int $recurrenceMetadata,
        EventLocationDto $eventLocation,
        DateTime $startFirstOccurrence,
        DateTime $endFirstOccurrence,
        DateTime | null $endRecurrence,
        FileDto $fileDto,
    ) {
        $this->guid = $guid;
        $this->titleDe = $titleDe;
        $this->titleEn = $titleEn;
        $this->descriptionDe = $descriptionDe;
        $this->descriptionEn = $descriptionEn;
        $this->recurrence = $recurrence;
        $this->recurrenceMetadata = $recurrenceMetadata;
        $this->eventLocation = $eventLocation;
        $this->startFirstOccurrence = $startFirstOccurrence;
        $this->endFirstOccurrence = $endFirstOccurrence;
        $this->endRecurrence = $endRecurrence;
        $this->image = $fileDto;
    }

    public static function create(RecurringEvent $recurringEvent, ?EventLocation $eventLocation = null, ?FileUpload $fileUpload = null): RecurringEventDto {
        $eventLocation = $eventLocation ?? $recurringEvent->eventLocation()->first();
        $fileUpload = $fileUpload ?? $recurringEvent->fileUpload()->first();
        return new RecurringEventDto(
            $recurringEvent->guid,
            $recurringEvent->title_de,
            $recurringEvent->title_en,
            $recurringEvent->description_de,
            $recurringEvent->description_en,
            $recurringEvent->recurrence,
            $recurringEvent->recurrence_metadata,
            EventLocationDto::create($eventLocation),
            $recurringEvent->start_first_occurrence,
            $recurringEvent->end_first_occurrence,
            $recurringEvent->end_recurrence,
            FileDto::create($fileUpload)
        );
    }
}
