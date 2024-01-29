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
        string $title_de,
        string $title_en,
        string $description_de,
        string $description_en,
        Recurrence $recurrence,
        int $recurrenceMetadata,
        EventLocationDto $eventLocation,
        DateTime $startFirstOccurrence,
        DateTime $endFirstOccurrence,
        DateTime | null $endRecurrence,
        FileDto $fileDto,
    ) {
        $this->title_de = $title_de;
        $this->title_en = $title_en;
        $this->description_de = $description_de;
        $this->description_en = $description_en;
        $this->recurrence = $recurrence;
        $this->recurrenceMetadata = $recurrenceMetadata;
        $this->eventLocation = $eventLocation;
        $this->startFirstOccurrence = $startFirstOccurrence;
        $this->endFirstOccurrence = $endFirstOccurrence;
        $this->endRecurrence = $endRecurrence;
        $this->image = $fileDto;
    }

    public static function create(RecurringEvent $recurringEvent, EventLocation $eventLocation, FileUpload $fileUpload): RecurringEventDto {
        return new RecurringEventDto(
            $recurringEvent->default_title_de,
            $recurringEvent->default_title_en,
            $recurringEvent->default_description_de,
            $recurringEvent->default_description_en,
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
