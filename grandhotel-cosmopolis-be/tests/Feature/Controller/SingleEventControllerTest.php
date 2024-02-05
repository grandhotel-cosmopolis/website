<?php

namespace Tests\Feature\Controller;

use App\Http\Dtos\Event\SingleEventDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\Permissions;
use App\Models\SingleEvent;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SingleEventControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $basePath = "/api/singleEvent";

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /** @test */
    public function getSingleEvents_default_call_returnsAllSingleEvents() {
        // Arrange
        /** @var SingleEvent $createdEvent */
        $createdEvent = static::createSingleEvent()
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2)
            ]);

        // Act
        $response = $this->get('/api/singleEvent/list', ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(1, $events);

        $event = $events[0];
        $this->assertEquals($createdEvent->title_de, $event['title_de']);
        $this->assertEquals($createdEvent->title_en, $event['title_en']);
        $this->assertEquals($createdEvent->description_de, $event['description_de']);
        $this->assertEquals($createdEvent->description_en, $event['description_en']);
        $this->assertEquals($createdEvent->start, new Carbon($event['start']));
        $this->assertEquals($createdEvent->end, new Carbon($event['end']));

        $this->assertEquals('image/png', $event['image']['mimeType']);
        /** @var FileUpload $fileUpload */
        $fileUpload = $createdEvent->fileUpload()->first();
        $this->assertStringContainsString($fileUpload->file_path, $event['image']['fileUrl']);

    }

    /** @test */
    public function getSingleEvents_noEventsInNext3Weeks_returnsEmptyList () {
        // Arrange
        /** @var SingleEvent $createdEvent */
        static::createSingleEvent()
            ->create([
                'start' => Carbon::now()->addMonth(),
                'end' => Carbon::now()->addMonth()->addHours(2)
            ]);

        // Act
        $response = $this->get('/api/singleEvent/list', ['Accept' => 'application/json']);
        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(0, $events);
    }

    /** @test  */
    public function getSingleEvents_specifiedStartAndEnd_returnsOnlyEventsInRange() {
        // Arrange
        $start = '2024-01-06T00:00:00.000Z';
        $end = '2024-01-11T00:00:00.000Z';

        $expectedEventsCount = 3;

        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::factory()->create();
        // Events within requested time range
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-06T16:30:00.000Z'),
            'end' => new Carbon('2024-01-06T18:00:00.000Z')
        ]);
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-04T16:30:00.000Z'),
            'end' => new Carbon('2024-01-06T18:00:00.000Z')
        ]);
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-10T16:30:00.000Z'),
            'end' => new Carbon('2024-01-12T18:00:00.000Z')
        ]);
        // Event before requested time range
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-05T16:30:00.000Z'),
            'end' => new Carbon('2024-01-05T18:00:00.000Z')
        ]);
        // Event after requested time range
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-11T16:30:00.000Z'),
            'end' => new Carbon('2024-01-11T18:00:00.000Z')
        ]);

        // Act
        $response = $this->get("/api/singleEvent/list?start=$start&end=$end", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount($expectedEventsCount, $events);
    }

    /** @test */
    public function getSingleEvents_onlyStart_returnsBadRequest() {
        // Arrange
        $start = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvent/list?start=$start", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    /** @test */
    public function getSingleEvents_onlyEnd_returnsBadRequest() {
        // Arrange
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvent/list?end=$end", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    /** @test */
    public function getSingleEvents_startIsAfterEnd_returnsBadRequest() {
        // Arrange
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvent/list?start=$start&end=$end", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    /** @test */
    public function getSingleEvents_startIsNotParsable_returnsBadRequest() {
        // Arrange
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvent/list?start=$start&end=$end", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    /** @test */
    public function addSingleEvent_notLoggedIn_returnsUnauthenticated() {
        // Act
        $response = $this->put('/api/singleEvent', [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function addSingleEvent_notParsableTime_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT->value);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_en' => $eventData['title_en'],
                'title_de' => $eventData['title_de'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $start,
                'end' => $end
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.start'));
    }

    /** @test */
    public function addSingleEvent_startIsAfterEnd_returnsValidationError() {
        // Arrange
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_en' => $eventData['title_en'],
                'title_de' => $eventData['title_de'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $start,
                'end' => $end
            ]);

        // Assert
        $response->assertStatus(400);
        $response->assertContent('invalid time range');
    }

    /** @test */
    public function addSingleEvent_onlyEnd_returnsValidationError() {
        // Arrange
        $end = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);

        // Act
        $response = $this->actingAs($user)
            ->put('/api/singleEvent', ['end' => $end], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function addSingleEvent_onlyStart_returnsValidationError() {
        // Arrange
        $start = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);

        // Act
        $response = $this->actingAs($user)
            ->put('/api/singleEvent', ['start' => $start], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function addSingleEvent_missingTitleDe_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.title_de'));
    }

    /** @test */
    public function addSingleEvent_missingTitleEn_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => $eventData['title_de'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.title_en'));
    }

    /** @test */
    public function addSingleEvent_missingDescriptionDE_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.description_de'));
    }

    /** @test */
    public function addSingleEvent_missingDescriptionEN_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.description_en'));
    }

    /** @test */
    public function addSingleEvent_missingEventLocation_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.event_location_guid'));
    }

    /** @test */
    public function addSingleEvent_nonExistingEventLocation_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $nonExistingEventLocationGuid = uuid_create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $nonExistingEventLocationGuid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(404);
        $response->assertContent('not found');
    }

    /** @test */
    public function addSingleEvent_invalidTitleDeObject_returnsBadRequest() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => [
                    'bla' => 'bla'
                ],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.title_de'));
    }

    /** @test */
    public function addSingleEvent_validEvent_eventIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(200);
        $allEvents = SingleEvent::all();
        $this->assertCount(1, $allEvents);
        /** @var SingleEvent $newEvent */
        $newEvent = $allEvents[0];
        /** @var EventLocation $newEventLocation */
        $newEventLocation = $newEvent->eventLocation()->first();
        $this->assertEquals($eventData['title_de'], $newEvent->title_de);
        $this->assertEquals($eventData['title_en'], $newEvent->title_en);
        $this->assertEquals($eventData['description_de'], $newEvent->description_de);
        $this->assertEquals($eventData['description_en'], $newEvent->description_en);
        $this->assertEquals(new Carbon($eventData['start']), $newEvent->start);
        $this->assertEquals(new Carbon($eventData['end']), $newEvent->end);
        $this->assertEquals($eventLocation->name, $newEventLocation->name);
        $this->assertEquals($eventLocation->street, $newEventLocation->street);
        $this->assertEquals($eventLocation->city, $newEventLocation->city);
    }

    /** @test */
    public function addSingleEvent_validEvent_createdEventIsReturned() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('title_de', $eventData['title_de'])
                ->where('title_en', $eventData['title_en'])
                ->where('description_de', $eventData['description_de'])
                ->where('description_en', $eventData['description_en'])
                ->where('start', fn (string $start) => new Carbon($start) == new Carbon($eventData['start']))
                ->where('end', fn (string $end) => new Carbon($end) == new Carbon($eventData['end']))
                ->where('eventLocation.name', $eventLocation->name)
                ->where('eventLocation.street', $eventLocation->street)
                ->where('eventLocation.city', $eventLocation->city)
                ->where('image.fileUrl', 'http://localhost:8000/storage/' . $fileUpload->file_path)
                ->where('image.mimeType', 'image/png')
        );
    }

    /** @test */
    public function addSingleEvent_userIsNotAuthorized_returnsForbidden() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->putJson('/api/singleEvent', [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function edit_notLoggedIn_returnsUnauthenticated() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->post("$this->basePath/$oldEvent->guid/edit", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function edit_notParsableTime_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_en' => $eventData['title_en'],
                'title_de' => $eventData['title_de'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $start,
                'end' => $end
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.start'));
    }

    /** @test */
    public function edit_startIsAfterEnd_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_en' => $eventData['title_en'],
                'title_de' => $eventData['title_de'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $start,
                'end' => $end
            ]);

        // Assert
        $response->assertStatus(400);
        $response->assertContent('invalid time range');
    }

    /** @test */
    public function edit_onlyEnd_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $end = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);

        // Act
        $response = $this->actingAs($user)
            ->post("$this->basePath/$oldEvent->guid/edit", ['end' => $end], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function edit_onlyStart_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $start = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);

        // Act
        $response = $this->actingAs($user)
            ->post("$this->basePath/$oldEvent->guid/edit", ['start' => $start], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function edit_missingTitleDe_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.title_de'));
    }

    /** @test */
    public function edit_missingTitleEn_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => $eventData['title_de'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.title_en'));
    }

    /** @test */
    public function edit_missingDescriptionDE_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.description_de'));
    }

    /** @test */
    public function edit_missingDescriptionEN_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.description_en'));
    }

    /** @test */
    public function edit_missingEventLocation_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.event_location_guid'));
    }

    /** @test */
    public function edit_nonExistingEventLocation_returnsValidationError() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $nonExistingEventLocationGuid = uuid_create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $nonExistingEventLocationGuid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(404);
        $response->assertContent('not found');
    }

    /** @test */
    public function edit_invalidTitleDeObject_returnsBadRequest() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => [
                    'bla' => 'bla'
                ],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.title_de'));
    }

    /** @test */
    public function edit_validEvent_eventIsStoredInDb() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(200);
        $allEvents = SingleEvent::all();
        $this->assertCount(1, $allEvents);
        /** @var SingleEvent $newEvent */
        $newEvent = $allEvents[0];
        /** @var EventLocation $newEventLocation */
        $newEventLocation = $newEvent->eventLocation()->first();
        $this->assertEquals($eventData['title_de'], $newEvent->title_de);
        $this->assertEquals($eventData['title_en'], $newEvent->title_en);
        $this->assertEquals($eventData['description_de'], $newEvent->description_de);
        $this->assertEquals($eventData['description_en'], $newEvent->description_en);
        $this->assertEquals(new Carbon($eventData['start']), $newEvent->start);
        $this->assertEquals(new Carbon($eventData['end']), $newEvent->end);
        $this->assertEquals($eventLocation->name, $newEventLocation->name);
        $this->assertEquals($eventLocation->street, $newEventLocation->street);
        $this->assertEquals($eventLocation->city, $newEventLocation->city);
    }

    /** @test */
    public function edit_validEvent_createdEventIsReturned() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->where('title_de', $eventData['title_de'])
            ->where('title_en', $eventData['title_en'])
            ->where('description_de', $eventData['description_de'])
            ->where('description_en', $eventData['description_en'])
            ->where('start', fn (string $start) => new Carbon($start) == new Carbon($eventData['start']))
            ->where('end', fn (string $end) => new Carbon($end) == new Carbon($eventData['end']))
            ->where('eventLocation.name', $eventLocation->name)
            ->where('eventLocation.street', $eventLocation->street)
            ->where('eventLocation.city', $eventLocation->city)
            ->where('image.fileUrl', 'http://localhost:8000/storage/' . $fileUpload->file_path)
            ->where('image.mimeType', 'image/png')
        );
    }

    /** @test */
    public function edit_userIsNotAuthorized_returnsForbidden() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson("$this->basePath/$oldEvent->guid/edit", [
                'title_de' => $eventData['title_de'],
                'title_en' => $eventData['title_en'],
                'description_de' => $eventData['description_de'],
                'description_en' => $eventData['description_en'],
                'event_location_guid' => $eventLocation->guid,
                'file_upload_guid' => $fileUpload->guid,
                'start' => $eventData['start'],
                'end' => $eventData['end']
            ]);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function delete_notAuthenticated_returnsUnauthenticated() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->delete("$this->basePath/$oldEvent->guid", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function delete_notAuthorized_returnsUnauthorized() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->delete("$this->basePath/$oldEvent->guid", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function delete_unknownEvent_returnsNotFound() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DELETE_EVENT);

        // Act
        $response = $this->actingAs($user)->delete("$this->basePath/unknown", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function delete_allValid_eventIsDeleted() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DELETE_EVENT);

        // Act
        $response = $this->actingAs($user)->delete("$this->basePath/$oldEvent->guid", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals(0, SingleEvent::query()->where('guid', $oldEvent->guid)->count());
    }

    /** @test */
    public function publish_notAuthenticated_returnsUnauthenticated() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->post("$this->basePath/$oldEvent->guid/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function publish_notAuthorized_returnsUnauthorized() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/$oldEvent->guid/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function publish_unknownEvent_returnsNotFound() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PUBLISH_EVENT);

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/unknown/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function publish_allValid_eventIsPublished() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create(['is_public' => false]);
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PUBLISH_EVENT);

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/$oldEvent->guid/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEvent $updatedEvent */
        $updatedEvent = SingleEvent::query()->where('guid', $oldEvent->guid)->first();
        $this->assertTrue($updatedEvent->is_public);
    }

    /** @test */
    public function unpublish_notAuthenticated_returnsUnauthenticated() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->post("$this->basePath/$oldEvent->guid/unpublish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function unpublish_notAuthorized_returnsUnauthorized() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/$oldEvent->guid/unpublish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function unpublish_unknownEvent_returnsNotFound() {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::UNPUBLISH_EVENT);

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/unknown/unpublish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function unpublish_allValid_eventIsUnpublished() {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create(['is_public' => true]);
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::UNPUBLISH_EVENT);

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/$oldEvent->guid/unpublish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEvent $updatedEvent */
        $updatedEvent = SingleEvent::query()->where('guid', $oldEvent->guid)->first();
        $this->assertFalse($updatedEvent->is_public);
    }

    private static function getTestEventData(): array {
        return [
            'title_de' => 'test title de',
            'title_en' => 'test title en',
            'description_de' => 'test description de',
            'description_en' => 'test description en',
            'eventLocation_name' => 'test eventLocation name',
            'eventLocation_street' => 'test eventLocation street',
            'eventLocation_city' => 'test eventLocation city',
            'start' => '2024-01-06T16:30:00.0Z',
            'end' => '2024-01-06T18:30:00.0Z'
        ];
    }

    private static function createSingleEvent(
        ?EventLocation $eventLocation = null,
        ?User $user = null,
        ?FileUpload $fileUpload = null
    ): Factory {
        $eventLocation = !is_null($eventLocation) ? $eventLocation : EventLocation::factory()->create();
        $user = !is_null($user) ? $user : User::factory()->create();
        $fileUpload = !is_null($fileUpload) ? $fileUpload : FileUpload::factory()->for($user, 'uploadedBy')->create();
        return SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy');
    }
}
