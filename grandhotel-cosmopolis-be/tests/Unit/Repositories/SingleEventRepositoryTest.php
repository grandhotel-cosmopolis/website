<?php

namespace Tests\Unit\Repositories;

use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\User;
use App\Repositories\SingleEventRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class SingleEventRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SingleEventRepository $cut;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->cut = new SingleEventRepository();
    }

    /** @test */
    public function createSingleEvent_NotExistingEventLocation_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $eventLocation = EventLocation::factory()->create();
        $singleEvent = SingleEvent::factory()->for($eventLocation)->make();

        // Act & Assert
        $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $eventLocation->guid,
            'not existing'
        );
    }

    /** @test */
    public function createSingleEvent_NotExistingFileUpload_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $fileUpload = FileUpload::factory()->for(User::factory()->create(), 'uploadedBy')->create();
        $singleEvent = SingleEvent::factory()->make();

        // Act & Assert
        $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            'not existing',
            $fileUpload->guid
        );
    }

    /** @test */
    public function createSingleEvent_allValid_createdEventIsReturned() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $singleEvent = SingleEvent::factory()->make();
        $this->be($user);

        // Act
        $createdEvent = $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $eventLocation->guid,
            $fileUpload->guid
        );

        // Assert
        $this->assertNotEquals($singleEvent->guid, $createdEvent->guid);
        $this->assertEquals($singleEvent->title_de, $createdEvent->title_de);
        $this->assertEquals($singleEvent->title_en, $createdEvent->title_en);
        $this->assertEquals($singleEvent->description_de, $createdEvent->description_de);
        $this->assertEquals($singleEvent->description_en, $createdEvent->description_en);
        $this->assertEquals($singleEvent->start, $createdEvent->start);
        $this->assertEquals($singleEvent->end, $createdEvent->end);
        $this->assertFalse($createdEvent->is_public);
        $this->assertFalse($createdEvent->is_recurring);
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
        $singleEvent = SingleEvent::factory()->make();
        $this->be($user);

        // Act
        $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $eventLocation->guid,
            $fileUpload->guid
        );

        // Assert
        $events = SingleEvent::query()
            ->where('title_de', $singleEvent->title_de)
            ->where('title_en', $singleEvent->title_en)
            ->get();
        $this->assertCount(1, $events);
        $createdEvent = $events[0];
        $this->assertNotEquals($singleEvent->guid, $createdEvent->guid);
        $this->assertEquals($singleEvent->title_de, $createdEvent->title_de);
        $this->assertEquals($singleEvent->title_en, $createdEvent->title_en);
        $this->assertEquals($singleEvent->description_de, $createdEvent->description_de);
        $this->assertEquals($singleEvent->description_en, $createdEvent->description_en);
        $this->assertEquals($singleEvent->start, $createdEvent->start);
        $this->assertEquals($singleEvent->end, $createdEvent->end);
        $this->assertFalse($createdEvent->is_public);
        $this->assertFalse($createdEvent->is_recurring);
        $this->assertEquals($eventLocation->guid, $createdEvent->eventLocation()->first()->guid);
        $this->assertEquals($fileUpload->guid, $createdEvent->fileUpload()->first()->guid);
    }

    /** @test */
    public function createSingleEvent_allValidPublicEvent_newPublicEventIsCreated() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $singleEvent = SingleEvent::factory()->make();
        $this->be($user);

        // Act
        $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            true,
            $eventLocation->guid,
            $fileUpload->guid
        );

        // Assert
        $events = SingleEvent::query()
            ->where('title_de', $singleEvent->title_de)
            ->where('title_en', $singleEvent->title_en)
            ->get();
        $this->assertCount(1, $events);
        $createdEvent = $events[0];
        $this->assertNotEquals($singleEvent->guid, $createdEvent->guid);
        $this->assertEquals($singleEvent->title_de, $createdEvent->title_de);
        $this->assertEquals($singleEvent->title_en, $createdEvent->title_en);
        $this->assertEquals($singleEvent->description_de, $createdEvent->description_de);
        $this->assertEquals($singleEvent->description_en, $createdEvent->description_en);
        $this->assertEquals($singleEvent->start, $createdEvent->start);
        $this->assertEquals($singleEvent->end, $createdEvent->end);
        $this->assertTrue($createdEvent->is_public);
        $this->assertFalse($createdEvent->is_recurring);
        $this->assertEquals($eventLocation->guid, $createdEvent->eventLocation()->first()->guid);
        $this->assertEquals($fileUpload->guid, $createdEvent->fileUpload()->first()->guid);
    }

    /** @test */
    public function updateSingleEvent_NotExistingEventLocation_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $singleEvent = SingleEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        // Act & Assert
        $this->cut->updateSingleEvent(
            $singleEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            'not existing',
            $fileUpload->guid,

        );
    }

    /** @test */
    public function updateSingleEvent_NotExistingFileUpload_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $singleEvent = SingleEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        // Act & Assert
        $this->cut->updateSingleEvent(
            $singleEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $eventLocation->guid,
            'not existing'
        );
    }

    /** @test */
    public function updateSingleEvent_NotExistingEventGuid_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $singleEvent = SingleEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        // Act & Assert
        $this->cut->updateSingleEvent(
            'not existing',
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $eventLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function updateSingleEvent_allValid_updatedEventIsReturned() {
        // Arrange
        $user = User::factory()->create();
        $newFileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $newEventLocation = EventLocation::factory()->create();
        $singleEvent = SingleEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        $newSingleEventData = SingleEvent::factory()->make();

        // Act
        $updatedEvent = $this->cut->updateSingleEvent(
            $singleEvent->guid,
            $newSingleEventData->title_de,
            $newSingleEventData->title_en,
            $newSingleEventData->description_de,
            $newSingleEventData->description_en,
            $newSingleEventData->start,
            $newSingleEventData->end,
            false,
            $newEventLocation->guid,
            $newFileUpload->guid
        );

        // Assert
        $this->assertNotEquals($newSingleEventData->guid, $updatedEvent->guid);
        $this->assertEquals($singleEvent->guid, $updatedEvent->guid);
        $this->assertEquals($newSingleEventData->title_de, $updatedEvent->title_de);
        $this->assertEquals($newSingleEventData->title_en, $updatedEvent->title_en);
        $this->assertEquals($newSingleEventData->description_de, $updatedEvent->description_de);
        $this->assertEquals($newSingleEventData->description_en, $updatedEvent->description_en);
        $this->assertEquals($newSingleEventData->start, $updatedEvent->start);
        $this->assertEquals($newSingleEventData->end, $updatedEvent->end);
        $this->assertFalse($updatedEvent->is_public);
        $this->assertFalse($updatedEvent->is_recurring);
        /** @var EventLocation $location */
        $location = $updatedEvent->eventLocation()->first();
        $this->assertEquals($newEventLocation->guid, $location->guid);
        /** @var FileUpload $upload */
        $upload = $updatedEvent->fileUpload()->first();
        $this->assertEquals($newFileUpload->guid, $upload->guid);
    }

    /** @test */
    public function updateSingleEvent_allValid_updatedEventIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $newFileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $newEventLocation = EventLocation::factory()->create();
        $singleEvent = SingleEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        $newSingleEventData = SingleEvent::factory()->make();

        // Act
        $this->cut->updateSingleEvent(
            $singleEvent->guid,
            $newSingleEventData->title_de,
            $newSingleEventData->title_en,
            $newSingleEventData->description_de,
            $newSingleEventData->description_en,
            $newSingleEventData->start,
            $newSingleEventData->end,
            false,
            $newEventLocation->guid,
            $newFileUpload->guid
        );

        // Assert
        $events = SingleEvent::query()
            ->where('title_de', $newSingleEventData->title_de)
            ->where('title_en', $newSingleEventData->title_en)
            ->get();
        $this->assertCount(1, $events);
        $updatedEvent = $events[0];
        $this->assertNotEquals($newSingleEventData->guid, $updatedEvent->guid);
        $this->assertEquals($singleEvent->guid, $updatedEvent->guid);
        $this->assertEquals($newSingleEventData->title_de, $updatedEvent->title_de);
        $this->assertEquals($newSingleEventData->title_en, $updatedEvent->title_en);
        $this->assertEquals($newSingleEventData->description_de, $updatedEvent->description_de);
        $this->assertEquals($newSingleEventData->description_en, $updatedEvent->description_en);
        $this->assertEquals($newSingleEventData->start, $updatedEvent->start);
        $this->assertEquals($newSingleEventData->end, $updatedEvent->end);
        $this->assertFalse($updatedEvent->is_public);
        $this->assertFalse($updatedEvent->is_recurring);
        $this->assertEquals($newEventLocation->guid, $updatedEvent->eventLocation()->first()->guid);
        $this->assertEquals($newFileUpload->guid, $updatedEvent->fileUpload()->first()->guid);
    }

    /** @test */
    public function updateSingleEvent_allValidPublicEvent_updatedEventIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $newFileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $newEventLocation = EventLocation::factory()->create();
        $singleEvent = SingleEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->for($user, 'createdBy')
            ->create();

        $newSingleEventData = SingleEvent::factory()->make();

        // Act
        $this->cut->updateSingleEvent(
            $singleEvent->guid,
            $newSingleEventData->title_de,
            $newSingleEventData->title_en,
            $newSingleEventData->description_de,
            $newSingleEventData->description_en,
            $newSingleEventData->start,
            $newSingleEventData->end,
            true,
            $newEventLocation->guid,
            $newFileUpload->guid
        );

        // Assert
        $events = SingleEvent::query()
            ->where('title_de', $newSingleEventData->title_de)
            ->where('title_en', $newSingleEventData->title_en)
            ->get();
        $this->assertCount(1, $events);
        $updatedEvent = $events[0];
        $this->assertNotEquals($newSingleEventData->guid, $updatedEvent->guid);
        $this->assertEquals($singleEvent->guid, $updatedEvent->guid);
        $this->assertEquals($newSingleEventData->title_de, $updatedEvent->title_de);
        $this->assertEquals($newSingleEventData->title_en, $updatedEvent->title_en);
        $this->assertEquals($newSingleEventData->description_de, $updatedEvent->description_de);
        $this->assertEquals($newSingleEventData->description_en, $updatedEvent->description_en);
        $this->assertEquals($newSingleEventData->start, $updatedEvent->start);
        $this->assertEquals($newSingleEventData->end, $updatedEvent->end);
        $this->assertTrue($updatedEvent->is_public);
        $this->assertFalse($updatedEvent->is_recurring);
        $this->assertEquals($newEventLocation->guid, $updatedEvent->eventLocation()->first()->guid);
        $this->assertEquals($newFileUpload->guid, $updatedEvent->fileUpload()->first()->guid);
    }

    /** @test */
    public function deleteSingleEvent_notExistingEvent_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        $this->cut->deleteSingleEvent('not existing');
    }

    /** @test */
    public function deleteSingleEvent_existingEvent_eventIsDeleted() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create();

        // Act
        $this->cut->deleteSingleEvent($event->guid);

        // Assert
        $this->assertCount(
            0,
            SingleEvent::query()
                ->where('guid', $event->guid)
                ->get());
    }

    /** @test */
    public function deleteSingleEvent_existingEvent_foreignModelsAreNotDeleted() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create();

        // Act
        $this->cut->deleteSingleEvent($event->guid);

        // Assert
        $this->assertCount(
            0,
            SingleEvent::query()
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
    public function publishSingleEvent_notExistingEvent_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        $this->cut->publishSingleEvent('not existing');
    }

    /** @test */
    public function publishSingleEvent_existingPrivateEvent_eventIsPublished() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create(['is_public' => false]);

        // Act
        $returnedEvent = $this->cut->publishSingleEvent($event->guid);

        // Assert
        /** @var SingleEvent $dbEvent */
        $dbEvent = SingleEvent::query()->where('guid', $event->guid)->first();
        $this->assertTrue($returnedEvent->is_public);
        $this->assertTrue($dbEvent->is_public);
    }

    /** @test */
    public function publishSingleEvent_existingPublicEvent_noChanges() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create(['is_public' => true]);

        // Act
        $returnedEvent = $this->cut->publishSingleEvent($event->guid);

        // Assert
        /** @var SingleEvent $dbEvent */
        $dbEvent = SingleEvent::query()->where('guid', $event->guid)->first();
        $this->assertTrue($returnedEvent->is_public);
        $this->assertTrue($dbEvent->is_public);
    }

    /** @test */
    public function unpublishSingleEvent_notExistingEvent_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        $this->cut->unpublishSingleEvent('not existing');
    }

    /** @test */
    public function unpublishSingleEvent_existingPrivateEvent_noChanges() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create(['is_public' => false]);

        // Act
        $returnedEvent = $this->cut->unpublishSingleEvent($event->guid);

        // Assert
        /** @var SingleEvent $dbEvent */
        $dbEvent = SingleEvent::query()->where('guid', $event->guid)->first();
        $this->assertFalse($returnedEvent->is_public);
        $this->assertFalse($dbEvent->is_public);
    }

    /** @test */
    public function unpublishSingleEvent_existingPublicEvent_eventIsUnpublished() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventLocation = EventLocation::factory()->create();
        $event = SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy')
            ->create(['is_public' => true]);

        // Act
        $returnedEvent = $this->cut->unpublishSingleEvent($event->guid);

        // Assert
        /** @var SingleEvent $dbEvent */
        $dbEvent = SingleEvent::query()->where('guid', $event->guid)->first();
        $this->assertFalse($returnedEvent->is_public);
        $this->assertFalse($dbEvent->is_public);
    }

    /** @test */
    public function getSingleEvents_allValid_returnsCorrectSingleEvents() {
        // Arrange
        $user = User::factory()->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => true
            ]);

        // Act
        $events = $this->cut->getSingleEvents(Carbon::now(), Carbon::now()->addWeeks(3));

        // Assert
        /** @var SingleEvent $event */
        $event = $events[0];
        $this->assertCount(1, $events);
        $this->assertEquals($singleEvent->title_de, $event->title_de);
        $this->assertEquals($singleEvent->title_en, $event->title_en);
        $this->assertEquals($singleEvent->description_de, $event->description_de);
        $this->assertEquals($singleEvent->description_en, $event->description_en);
        $this->assertEquals($singleEvent->start, $event->start);
        $this->assertEquals($singleEvent->end, $event->end);

    }

    /** @test */
    public function getSingleEvents_noEventsInTimeRange_returnsEmptyList() {
        // Act
        $events = $this->cut->getSingleEvents(Carbon::now(), Carbon::now()->addMinutes());

        // Assert
        $this->assertCount(0, $events);
    }

    /** @test */
    public function getSingleEvents_specifiedStartAndEnd_returnsOnlyEventsInRange() {
        // Arrange
        $start = Carbon::parse('2024-01-06T00:00:00.000Z');
        $end = Carbon::parse('2024-01-11T00:00:00.000Z');

        $expectedEventsCount = 3;

        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::factory()->create();
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        SingleEvent::factory()->for($eventLocation)->for($fileUpload)->for($user, 'createdBy')
            ->create([
                'start' => new Carbon('2024-01-06T16:30:00.000Z'),
                'end' => new Carbon('2024-01-06T18:00:00.000Z'),
                'is_public' => true
            ]);
        SingleEvent::factory()->for($eventLocation)->for($fileUpload)->for($user, 'createdBy')
            ->create([
                'start' => new Carbon('2024-01-04T16:30:00.000Z'),
                'end' => new Carbon('2024-01-06T18:00:00.000Z'),
                'is_public' => true
            ]);
        SingleEvent::factory()->for($eventLocation)->for($fileUpload)->for($user, 'createdBy')
            ->create([
                'start' => new Carbon('2024-01-10T16:30:00.000Z'),
                'end' => new Carbon('2024-01-12T18:00:00.000Z'),
                'is_public' => true
            ]);
        // Event before requested time range
        SingleEvent::factory()->for($eventLocation)->for($fileUpload)->for($user, 'createdBy')
            ->create([
                'start' => new Carbon('2024-01-05T16:30:00.000Z'),
                'end' => new Carbon('2024-01-05T18:00:00.000Z'),
                'is_public' => true
            ]);
        // Event after requested time range
        SingleEvent::factory()->for($eventLocation)->for($fileUpload)->for($user, 'createdBy')
            ->create([
                'start' => new Carbon('2024-01-11T16:30:00.000Z'),
                'end' => new Carbon('2024-01-11T18:00:00.000Z'),
                'is_public' => true
            ]);

        // Act
        $events = $this->cut->getSingleEvents($start, $end);

        // Assert
        $this->assertCount($expectedEventsCount, $events);
    }

    /** @test */
    public function getSingleEvents_allValid_returnsOnlyPublicEvents() {
        // Arrange
        $user = User::factory()->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => true
            ]);

        SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => false
            ]);

        // Act
        $events = $this->cut->getSingleEvents(Carbon::now(), Carbon::now()->addWeeks(3));

        // Assert
        /** @var SingleEvent $event */
        $event = $events[0];
        $this->assertCount(1, $events);
        $this->assertEquals($singleEvent->title_de, $event->title_de);
        $this->assertEquals($singleEvent->title_en, $event->title_en);
        $this->assertEquals($singleEvent->description_de, $event->description_de);
        $this->assertEquals($singleEvent->description_en, $event->description_en);
        $this->assertEquals($singleEvent->start, $event->start);
        $this->assertEquals($singleEvent->end, $event->end);
    }

    /** @test */
    public function listAll_allValid_returnsCorrectAmountOfEvents() {
        // Arrange
        $user = User::factory()->create();
        SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->count(5)
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
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
        SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->count(5)
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => false
            ]);

        SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($user, 'uploadedBy')->create())
            ->count(5)
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => true
            ]);

        // Act
        $result = $this->cut->listAll();

        // Assert
        $this->assertCount(10, $result);
    }
}
