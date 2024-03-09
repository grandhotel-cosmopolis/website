<?php

namespace Tests\Unit\Repositories;

use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\User;
use App\Repositories\RecurringEventRepository;
use Carbon\Carbon;
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
            $fileUpload->guid,
            false
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
            'not existing',
            false
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
            $fileUpload->guid,
            false
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
    public function create_allValid_newEventIsStoredInDb() {
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
            $fileUpload->guid,
            false
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

    /** @test */
    public function create_publicEvent_newEventIsPublic() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $recurringEvent = RecurringEvent::factory()->make();
        $this->be($user);

        // Act
        $newEvent = $this->cut->create(
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
            $fileUpload->guid,
            true
        );

        // Assert
        $events = RecurringEvent::query()
            ->where('title_de', $recurringEvent->title_de)
            ->where('title_en', $recurringEvent->title_en)
            ->get();
        $this->assertCount(1, $events);
        /** @var RecurringEvent $createdEvent */
        $createdEvent = $events[0];
        $this->assertTrue($createdEvent->is_public);
        $this->assertTrue($newEvent->is_public);
    }

    /** @test */
    public function update_notExistingEventLocation_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $recurringEvent = RecurringEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        // Act & Assert
        $this->cut->update(
            $recurringEvent->guid,
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
            $fileUpload->guid,
            false
        );
    }

    /** @test */
    public function update_notExistingFileUpload_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $recurringEvent = RecurringEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        // Act & Assert
        $this->cut->update(
            $recurringEvent->guid,
            $recurringEvent->title_de,
            $recurringEvent->title_en,
            $recurringEvent->description_de,
            $recurringEvent->description_en,
            $recurringEvent->start_first_occurrence,
            $recurringEvent->end_first_occurrence,
            $recurringEvent->end_recurrence,
            $recurringEvent->recurrence,
            $recurringEvent->recurrence_metadata,
            $recurringEvent->guid,
            'not existing',
            false
        );
    }

    /** @test */
    public function update_notExistingEventGuid_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $recurringEvent = RecurringEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        // Act & Assert
        $this->cut->update(
            'not existing',
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
            $fileUpload->guid,
            false
        );
    }

    /** @test */
    public function update_allValid_updatedEventIsReturned() {
        // Arrange
        $user = User::factory()->create();
        $newFileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $newEventLocation = EventLocation::factory()->create();
        $recurringEvent = RecurringEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        $newSingleEventData = RecurringEvent::factory()->make();

        // Act
        $updatedEvent = $this->cut->update(
            $recurringEvent->guid,
            $newSingleEventData->title_de,
            $newSingleEventData->title_en,
            $newSingleEventData->description_de,
            $newSingleEventData->description_en,
            $newSingleEventData->start_first_occurrence,
            $newSingleEventData->end_first_occurrence,
            $newSingleEventData->end_recurrence,
            $newSingleEventData->recurrence,
            $newSingleEventData->recurrence_metadata,
            $newEventLocation->guid,
            $newFileUpload->guid,
            false
        );

        // Assert
        $this->assertNotEquals($newSingleEventData->guid, $updatedEvent->guid);
        $this->assertEquals($recurringEvent->guid, $updatedEvent->guid);
        $this->assertEquals($newSingleEventData->title_de, $updatedEvent->title_de);
        $this->assertEquals($newSingleEventData->title_en, $updatedEvent->title_en);
        $this->assertEquals($newSingleEventData->description_de, $updatedEvent->description_de);
        $this->assertEquals($newSingleEventData->description_en, $updatedEvent->description_en);
        $this->assertEquals($newSingleEventData->start_first_occurrence, $updatedEvent->start_first_occurrence);
        $this->assertEquals($newSingleEventData->end_first_occurrence, $updatedEvent->end_first_occurrence);
        $this->assertEquals($newSingleEventData->end_recurrence, $updatedEvent->end_recurrence);
        $this->assertEquals($newSingleEventData->recurrence, $updatedEvent->recurrence);
        $this->assertEquals($newSingleEventData->recurrence_metadata, $updatedEvent->recurrence_metadata);
        $this->assertFalse($updatedEvent->is_public);
        /** @var EventLocation $location */
        $location = $updatedEvent->eventLocation()->first();
        $this->assertEquals($newEventLocation->guid, $location->guid);
        /** @var FileUpload $upload */
        $upload = $updatedEvent->fileUpload()->first();
        $this->assertEquals($newFileUpload->guid, $upload->guid);
    }

    /** @test */
    public function update_allValid_updatedEventIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $newFileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $newEventLocation = EventLocation::factory()->create();
        $recurringEvent = RecurringEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        $newSingleEventData = RecurringEvent::factory()->make();

        // Act
        $this->cut->update(
            $recurringEvent->guid,
            $newSingleEventData->title_de,
            $newSingleEventData->title_en,
            $newSingleEventData->description_de,
            $newSingleEventData->description_en,
            $newSingleEventData->start_first_occurrence,
            $newSingleEventData->end_first_occurrence,
            $newSingleEventData->end_recurrence,
            $newSingleEventData->recurrence,
            $newSingleEventData->recurrence_metadata,
            $newEventLocation->guid,
            $newFileUpload->guid,
            false
        );

        // Assert
        $events = RecurringEvent::query()
            ->where('title_de', $newSingleEventData->title_de)
            ->where('title_en', $newSingleEventData->title_en)
            ->get();
        $this->assertCount(1, $events);
        /** @var RecurringEvent $updatedEvent */
        $updatedEvent = $events[0];
        $this->assertNotEquals($newSingleEventData->guid, $updatedEvent->guid);
        $this->assertEquals($recurringEvent->guid, $updatedEvent->guid);
        $this->assertEquals($newSingleEventData->title_de, $updatedEvent->title_de);
        $this->assertEquals($newSingleEventData->title_en, $updatedEvent->title_en);
        $this->assertEquals($newSingleEventData->description_de, $updatedEvent->description_de);
        $this->assertEquals($newSingleEventData->description_en, $updatedEvent->description_en);
        $this->assertEquals($newSingleEventData->start_first_occurrence, $updatedEvent->start_first_occurrence);
        $this->assertEquals($newSingleEventData->end_first_occurrence, $updatedEvent->end_first_occurrence);
        $this->assertEquals($newSingleEventData->end_recurrence, $updatedEvent->end_recurrence);
        $this->assertEquals($newSingleEventData->recurrence, $updatedEvent->recurrence);
        $this->assertEquals($newSingleEventData->recurrence_metadata, $updatedEvent->recurrence_metadata);
        $this->assertFalse($updatedEvent->is_public);
        /** @var EventLocation $location */
        $location = $updatedEvent->eventLocation()->first();
        $this->assertEquals($newEventLocation->guid, $location->guid);
        /** @var FileUpload $fileUpload */
        $fileUpload = $updatedEvent->fileUpload()->first();
        $this->assertEquals($newFileUpload->guid, $fileUpload->guid);
    }

    /** @test */
    public function delete_notExistingEvent_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        $this->cut->delete('not existing');
    }

    /** @test */
    public function delete_existingEvent_eventIsDeleted() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = RecurringEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create();

        // Act
        $this->cut->delete($event->guid);

        // Assert
        $this->assertCount(
            0,
            RecurringEvent::query()
                ->where('guid', $event->guid)
                ->get());
    }

    /** @test */
    public function delete_existingEvent_foreignModelsAreNotDeleted() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = RecurringEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create();

        // Act
        $this->cut->delete($event->guid);

        // Assert
        $this->assertCount(
            0,
            RecurringEvent::query()
                ->where('guid', $event->guid)
                ->get()
        );
        $this->assertCount(
            1,
            EventLocation::query()
                ->where('guid', $eventLocation->guid)
                ->get()
        );
        $this->assertCount(
            1,
            FileUpload::query()
                ->where('guid', $fileUpload->guid)
                ->get()
        );
        $this->assertCount(
            1,
            User::query()
                ->where('name', $user->name)
                ->where('email', $user->email)
                ->get()
        );
    }

    /** @test */
    public function publish_notExistingEvent_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        $this->cut->publish('not existing');
    }

    /** @test */
    public function publish_existingPrivateEvent_eventIsPublished() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = RecurringEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create(['is_public' => false]);

        // Act
        $returnedEvent = $this->cut->publish($event->guid);

        // Assert
        /** @var RecurringEvent $dbEvent */
        $dbEvent = RecurringEvent::query()->where('guid', $event->guid)->first();
        $this->assertTrue($returnedEvent->is_public);
        $this->assertTrue($dbEvent->is_public);
    }

    /** @test */
    public function publish_existingPublicEvent_noChanges() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = RecurringEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create(['is_public' => true]);

        // Act
        $returnedEvent = $this->cut->publish($event->guid);

        // Assert
        /** @var RecurringEvent $dbEvent */
        $dbEvent = RecurringEvent::query()->where('guid', $event->guid)->first();
        $this->assertTrue($returnedEvent->is_public);
        $this->assertTrue($dbEvent->is_public);
    }

    /** @test */
    public function unpublish_notExistingEvent_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        $this->cut->unpublish('not existing');
    }

    /** @test */
    public function unpublish_existingPrivateEvent_noChanges() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = RecurringEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create(['is_public' => false]);

        // Act
        $returnedEvent = $this->cut->unpublish($event->guid);

        // Assert
        /** @var RecurringEvent $dbEvent */
        $dbEvent = RecurringEvent::query()->where('guid', $event->guid)->first();
        $this->assertFalse($returnedEvent->is_public);
        $this->assertFalse($dbEvent->is_public);
    }

    /** @test */
    public function unpublish_existingPublicEvent_eventIsUnpublished() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = RecurringEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create(['is_public' => true]);

        // Act
        $returnedEvent = $this->cut->unpublish($event->guid);

        // Assert
        /** @var RecurringEvent $dbEvent */
        $dbEvent = RecurringEvent::query()->where('guid', $event->guid)->first();
        $this->assertFalse($returnedEvent->is_public);
        $this->assertFalse($dbEvent->is_public);
    }

    /** @test */
    public function listAll_allValid_returnsCorrectAmountOfEvents() {
        // Arrange
        $user = User::factory()->create();
        RecurringEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->count(5)
            ->create([
                'end_recurrence' => Carbon::now()->addDays(5)->addHours(2),
            ]);

        // Act
        $result = $this->cut->listAll();

        // Assert
        $this->assertCount(5, $result);
    }

    /** @test */
    public function listAll_allValid_returnsPublicAndPrivateEvents() {
        // Arrange
        $user = User::factory()->create();
        RecurringEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->count(5)
            ->create([
                'end_recurrence' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => false
            ]);

        RecurringEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->count(5)
            ->create([
                'end_recurrence' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => true
            ]);

        // Act
        $result = $this->cut->listAll();

        // Assert
        $this->assertCount(10, $result);
    }

    /** @test */
    public function listAll_allValid_returnsOnlyEventsWhichNotAlreadyEnded() {
        // Arrange
        $user = User::factory()->create();
        RecurringEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->count(5)
            ->create([
                'end_recurrence' => Carbon::now()->addDays(5)->addHours(2)
            ]);

        RecurringEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->count(5)
            ->create([
                'end_recurrence' => Carbon::now()->subDays(5),
            ]);

        // Act
        $result = $this->cut->listAll();

        // Assert
        $this->assertCount(5, $result);
    }
}
