<?php

namespace Tests\Unit\Repositories;

use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\User;
use App\Repositories\RecurringEventRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class RecurringEventRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RecurringEventRepository $cut;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->cut = new RecurringEventRepository();
    }

    /** @test */
    public function create_notExistingEventLocation_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $eventLocation = EventLocation::factory()->create();
        $recurringEvent = RecurringEvent::factory()->for($eventLocation)->make();
        $fileUpload = FileUpload::factory()->for(User::factory()->create(), 'uploadedBy')->create();

        // Act & Assert
        $this->cut->create(
            $recurringEvent->title_de,
            $recurringEvent->title_en,
            $recurringEvent->description_de,
            $recurringEvent->description_en,
            $recurringEvent->start_first_occurrence,
            $recurringEvent->end_first_occurrence,
            $recurringEvent->end_recurrence,
            $recurringEvent->recurrence,
            $recurringEvent->recurrence_metadata,
            'not existing',
            $fileUpload->guid
        );
    }

    /** @test */
    public function create_NotExistingFileUpload_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $recurringEvent = RecurringEvent::factory()->make();
        $eventLocation = EventLocation::factory()->create();

        // Act & Assert
        $this->cut->create(
            $recurringEvent->title_de,
            $recurringEvent->title_en,
            $recurringEvent->description_de,
            $recurringEvent->description_en,
            $recurringEvent->start_first_occurrence,
            $recurringEvent->end_first_occurrence,
            $recurringEvent->end_recurrence,
            $recurringEvent->recurrence,
            $recurringEvent->recurrence_metadata,
            $eventLocation->guid,
            'not existing'
        );
    }

    /** @test */
    public function create_allValid_createdEventIsReturned() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $recurringEvent = RecurringEvent::factory()->make();
        $this->be($user);

        // Act
        $createdEvent = $this->cut->create(
            $recurringEvent->title_de,
            $recurringEvent->title_en,
            $recurringEvent->description_de,
            $recurringEvent->description_en,
            $recurringEvent->start_first_occurrence,
            $recurringEvent->end_first_occurrence,
            $recurringEvent->end_recurrence,
            $recurringEvent->recurrence,
            $recurringEvent->recurrence_metadata,
            $eventLocation->guid,
            $fileUpload->guid
        );

        // Assert
        $this->assertNotEquals($recurringEvent->guid, $createdEvent->guid);
        $this->assertEquals($recurringEvent->title_de, $createdEvent->title_de);
        $this->assertEquals($recurringEvent->title_en, $createdEvent->title_en);
        $this->assertEquals($recurringEvent->description_de, $createdEvent->description_de);
        $this->assertEquals($recurringEvent->description_en, $createdEvent->description_en);
        $this->assertEquals($recurringEvent->start_first_occurrence, $createdEvent->start_first_occurrence);
        $this->assertEquals($recurringEvent->end_first_occurrence, $createdEvent->end_first_occurrence);
        $this->assertEquals($recurringEvent->end_recurrence, $createdEvent->end_recurrence);
        $this->assertEquals($recurringEvent->recurrence, $createdEvent->recurrence);
        $this->assertEquals($recurringEvent->recurrence_metadata, $createdEvent->recurrence_metadata);
        /** @var EventLocation $location */
        $location = $createdEvent->eventLocation()->first();
        $this->assertEquals($eventLocation->guid, $location->guid);
        /** @var FileUpload $upload */
        $upload = $createdEvent->fileUpload()->first();
        $this->assertEquals($fileUpload->guid, $upload->guid);
    }

    /** @test */
    public function createSingleEvent_allValid_newEventIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $recurringEvent = RecurringEvent::factory()->make();
        $this->be($user);

        // Act
        $this->cut->create(
            $recurringEvent->title_de,
            $recurringEvent->title_en,
            $recurringEvent->description_de,
            $recurringEvent->description_en,
            $recurringEvent->start_first_occurrence,
            $recurringEvent->end_first_occurrence,
            $recurringEvent->end_recurrence,
            $recurringEvent->recurrence,
            $recurringEvent->recurrence_metadata,
            $eventLocation->guid,
            $fileUpload->guid
        );
        // Assert
        $events = RecurringEvent::query()
            ->where('title_de', $recurringEvent->title_de)
            ->where('title_en', $recurringEvent->title_en)
            ->get();
        $this->assertCount(1, $events);
        /** @var RecurringEvent $createdEvent */
        $createdEvent = $events[0];
        $this->assertNotEquals($recurringEvent->guid, $createdEvent->guid);
        $this->assertEquals($recurringEvent->title_de, $createdEvent->title_de);
        $this->assertEquals($recurringEvent->title_en, $createdEvent->title_en);
        $this->assertEquals($recurringEvent->description_de, $createdEvent->description_de);
        $this->assertEquals($recurringEvent->description_en, $createdEvent->description_en);
        $this->assertEquals($recurringEvent->start_first_occurrence, $createdEvent->start_first_occurrence);
        $this->assertEquals($recurringEvent->end_first_occurrence, $createdEvent->end_first_occurrence);
        $this->assertEquals($recurringEvent->end_recurrence, $createdEvent->end_recurrence);
        $this->assertEquals($recurringEvent->recurrence, $createdEvent->recurrence);
        $this->assertEquals($recurringEvent->recurrence_metadata, $createdEvent->recurrence_metadata);
        /** @var EventLocation $location */
        $location = $createdEvent->eventLocation()->first();
        $this->assertEquals($eventLocation->guid, $location->guid);
        /** @var FileUpload $upload */
        $upload = $createdEvent->fileUpload()->first();
        $this->assertEquals($fileUpload->guid, $upload->guid);
    }
}
