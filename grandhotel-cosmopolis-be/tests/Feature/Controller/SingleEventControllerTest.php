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
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SingleEventControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $basePath = "/api/singleEvent";

    private User $user;
    private EventLocation $eventLocation;
    private FileUpload $fileUpload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->seed(RoleAndPermissionSeeder::class);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            Permissions::CREATE_EVENT->value,
            Permissions::EDIT_EVENT->value,
            Permissions::DELETE_EVENT->value,
            Permissions::PUBLISH_EVENT->value,
            Permissions::UNPUBLISH_EVENT->value,
            Permissions::VIEW_EVENTS->value
        ]);

        $this->eventLocation = EventLocation::factory()->create();
        $this->fileUpload = FileUpload::factory()->for($this->user, 'uploadedBy')->create();
    }

    /** @test */
    public function list_allValid_returnsAllSingleEvents()
    {
        // Arrange
        /** @var SingleEvent $createdEvent */
        $createdEvent = static::createSingleEvent()
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => true
            ]);

        // Act
        $response = $this->listRequest();

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(1, $events);

        $event = $events[0];
        $this->assertEquals($createdEvent->title_de, $event['titleDe']);
        $this->assertEquals($createdEvent->title_en, $event['titleEn']);
        $this->assertEquals($createdEvent->description_de, $event['descriptionDe']);
        $this->assertEquals($createdEvent->description_en, $event['descriptionEn']);
        $this->assertEquals($createdEvent->start, new Carbon($event['start']));
        $this->assertEquals($createdEvent->end, new Carbon($event['end']));

        $this->assertEquals('image/png', $event['image']['mimeType']);
        /** @var FileUpload $fileUpload */
        $fileUpload = $createdEvent->fileUpload()->first();
        $this->assertStringContainsString($fileUpload->file_path, $event['image']['fileUrl']);
    }

    /** @test */
    public function list_allValid_returnsOnlyPublicEvents()
    {
        // Arrange
        /** @var SingleEvent $createdEvent */
        $createdEvent = static::createSingleEvent()
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => true
            ]);

        static::createSingleEvent()
            ->create([
                'start' => Carbon::now()->addDays(5),
                'end' => Carbon::now()->addDays(5)->addHours(2),
                'is_public' => false
            ]);

        // Act
        $response = $this->listRequest();

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(1, $events);

        $event = $events[0];
        $this->assertEquals($createdEvent->title_de, $event['titleDe']);
        $this->assertEquals($createdEvent->title_en, $event['titleEn']);
        $this->assertEquals($createdEvent->description_de, $event['descriptionDe']);
        $this->assertEquals($createdEvent->description_en, $event['descriptionEn']);
        $this->assertEquals($createdEvent->start, new Carbon($event['start']));
        $this->assertEquals($createdEvent->end, new Carbon($event['end']));

        $this->assertEquals('image/png', $event['image']['mimeType']);
        /** @var FileUpload $fileUpload */
        $fileUpload = $createdEvent->fileUpload()->first();
        $this->assertStringContainsString($fileUpload->file_path, $event['image']['fileUrl']);
    }

    /** @test */
    public function list_noEventsInNext3Weeks_returnsEmptyList()
    {
        // Arrange
        /** @var SingleEvent $createdEvent */
        static::createSingleEvent()
            ->create([
                'start' => Carbon::now()->addMonth(),
                'end' => Carbon::now()->addMonth()->addHours(2)
            ]);

        // Act
        $response = $this->listRequest();

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(0, $events);
    }

    /** @test */
    public function list_specifiedStartAndEnd_returnsOnlyEventsInRange()
    {
        // Arrange
        $start = '2024-01-06T00:00:00.000Z';
        $end = '2024-01-11T00:00:00.000Z';

        $expectedEventsCount = 3;

        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::factory()->create();
        // Events within requested time range
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-06T16:30:00.000Z'),
            'end' => new Carbon('2024-01-06T18:00:00.000Z'),
            'is_public' => true
        ]);
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-04T16:30:00.000Z'),
            'end' => new Carbon('2024-01-06T18:00:00.000Z'),
            'is_public' => true
        ]);
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-10T16:30:00.000Z'),
            'end' => new Carbon('2024-01-12T18:00:00.000Z'),
            'is_public' => true
        ]);
        // Event before requested time range
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-05T16:30:00.000Z'),
            'end' => new Carbon('2024-01-05T18:00:00.000Z'),
            'is_public' => true
        ]);
        // Event after requested time range
        static::createSingleEvent($eventLocation)->create([
            'start' => new Carbon('2024-01-11T16:30:00.000Z'),
            'end' => new Carbon('2024-01-11T18:00:00.000Z'),
            'is_public' => true
        ]);

        // Act
        $response = $this->listRequest($start, $end);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount($expectedEventsCount, $events);
    }

    /** @test */
    public function list_onlyStart_returnsBadRequest()
    {
        // Arrange
        $start = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->listRequest($start);

        // Assert
        $response->assertStatus(400);
    }

    /** @test */
    public function list_onlyEnd_returnsBadRequest()
    {
        // Arrange
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->listRequest(end: $end);

        // Assert
        $response->assertStatus(400);
    }

    /** @test */
    public function list_startIsAfterEnd_returnsBadRequest()
    {
        // Arrange
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->listRequest($start, $end);

        // Assert
        $response->assertStatus(400);
    }

    /** @test */
    public function list_startIsNotParsable_returnsBadRequest()
    {
        // Arrange
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->listRequest($start, $end);

        // Assert
        $response->assertStatus(400);
    }

    /** @test */
    public function listAll_notLoggedIn_returnsUnauthorized()
    {
        // Act
        $response = $this->get("$this->basePath/listAll", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function listAll_unauthorized_returnsUnauthorized()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get("$this->basePath/listAll", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function listAll_allValid_returnsPublicAndPrivateEvents()
    {
        // Arrange
        self::createSingleEvent()
            ->count(10)
            ->create(['is_public' => true]);
        self::createSingleEvent()
            ->count(5)
            ->create(['is_public' => false]);

        // Act
        $response = $this->actingAs($this->user)->get("$this->basePath/listAll", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(15, $events);
    }

    /** @test */
    public function listAll_allValid_doesNotReturnEventsInThePast()
    {
        // Arrange
        self::createSingleEvent()
            ->count(10)
            ->create([
                'start' => new Carbon('2024-01-11T16:30:00.000Z'),
                'end' => new Carbon('2024-01-11T18:00:00.000Z')
            ]);
        self::createSingleEvent()
            ->count(5)
            ->create(['is_public' => false]);

        // Act
        $response = $this->actingAs($this->user)->get("$this->basePath/listAll", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(5, $events);
    }

    /** @test */
    public function create_notLoggedIn_returnsUnauthenticated()
    {
        // Act
        $response = $this->post("$this->basePath", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function create_notAuthorized_returnsUnauthorized()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->createRequest($user);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function create_notParsableTime_returnsValidationError()
    {
        // Arrange
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->createRequest(
            start: $start,
            end: $end
        );

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.start'));
    }

    /** @test */
    public function create_startIsAfterEnd_returnsValidationError()
    {
        // Arrange
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->createRequest(start: $start, end: $end);

        // Assert
        $response->assertStatus(400);
        $response->assertContent('invalid time range');
    }

    /** @test */
    public function create_onlyEnd_returnsValidationError()
    {
        // Arrange
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->actingAs($this->user)
            ->post('/api/singleEvent', ['end' => $end], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function create_onlyStart_returnsValidationError()
    {
        // Arrange
        $start = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->actingAs($this->user)
            ->post('/api/singleEvent', ['start' => $start], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function create_missingTitleDe_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(titleDe: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.titleDe'));
    }

    /** @test */
    public function create_missingTitleEn_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(titleEn: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.titleEn'));
    }

    /** @test */
    public function create_missingDescriptionDE_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(descriptionDe: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.descriptionDe'));
    }

    /** @test */
    public function create_missingDescriptionEn_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(descriptionEn: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.descriptionEn'));
    }

    /** @test */
    public function create_missingEventLocation_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(eventLocationGuid: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.eventLocationGuid'));
    }

    /** @test */
    public function create_nonExistingEventLocation_returnsValidationError()
    {
        // Act
        $response = $this->createRequest(eventLocationGuid: uuid_create());

        // Assert
        $response->assertStatus(404);
        $response->assertContent('not found');
    }

    /** @test */
    public function create_invalidTitleDeObject_returnsBadRequest()
    {
        // Act
        $response = $this->createRequest(titleDe: ['bla' => 'bla']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.titleDe'));
    }

    /** @test */
    public function create_validEvent_eventIsStoredInDb()
    {
        // Arrange
        $eventData = static::getTestEventData();

        // Act
        $response = $this->createRequest();

        // Assert
        $response->assertStatus(200);
        $allEvents = SingleEvent::all();
        $this->assertCount(1, $allEvents);
        /** @var SingleEvent $newEvent */
        $newEvent = $allEvents[0];
        /** @var EventLocation $newEventLocation */
        $newEventLocation = $newEvent->eventLocation()->first();
        $this->assertEquals($eventData['titleDe'], $newEvent->title_de);
        $this->assertEquals($eventData['titleEn'], $newEvent->title_en);
        $this->assertEquals($eventData['descriptionDe'], $newEvent->description_de);
        $this->assertEquals($eventData['descriptionEn'], $newEvent->description_en);
        $this->assertEquals(new Carbon($eventData['start']), $newEvent->start);
        $this->assertEquals(new Carbon($eventData['end']), $newEvent->end);
        $this->assertEquals($this->eventLocation->name, $newEventLocation->name);
        $this->assertEquals($this->eventLocation->street, $newEventLocation->street);
        $this->assertEquals($this->eventLocation->city, $newEventLocation->city);
    }

    /** @test */
    public function create_validEvent_createdEventIsReturned()
    {
        // Arrange
        $eventData = static::getTestEventData();

        // Act
        $response = $this->createRequest();

        // Assert
        $response->assertStatus(200);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('guid', fn(mixed $guid) => is_string($guid))
            ->where('titleDe', $eventData['titleDe'])
            ->where('titleEn', $eventData['titleEn'])
            ->where('descriptionDe', $eventData['descriptionDe'])
            ->where('descriptionEn', $eventData['descriptionEn'])
            ->where('start', fn(string $start) => new Carbon($start) == new Carbon($eventData['start']))
            ->where('end', fn(string $end) => new Carbon($end) == new Carbon($eventData['end']))
            ->where('eventLocation.name', $this->eventLocation->name)
            ->where('eventLocation.street', $this->eventLocation->street)
            ->where('eventLocation.city', $this->eventLocation->city)
            ->where('eventLocation.additionalInformation', $this->eventLocation->additional_information)
            ->where('image.fileUrl', 'http://localhost:8000/storage/' . $this->fileUpload->file_path)
            ->where('image.mimeType', 'image/png')
            ->where('isPublic', false)
        );
    }

    /** @test */
    public function update_notLoggedIn_returnsUnauthenticated()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->post("$this->basePath/$oldEvent->guid/update", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function update_notParsableTime_returnsValidationError()
    {
        // Arrange
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->updateRequest(start: $start, end: $end);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.start'));
    }

    /** @test */
    public function update_startIsAfterEnd_returnsValidationError()
    {
        // Arrange
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->updateRequest(start: $start, end: $end);

        // Assert
        $response->assertStatus(400);
        $response->assertContent('invalid time range');
    }

    /** @test */
    public function update_onlyEnd_returnsValidationError()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $end = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);

        // Act
        $response = $this->actingAs($user)
            ->post("$this->basePath/$oldEvent->guid/update", ['end' => $end], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function update_onlyStart_returnsValidationError()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();
        $start = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT);

        // Act
        $response = $this->actingAs($user)
            ->post("$this->basePath/$oldEvent->guid/update", ['start' => $start], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function update_missingTitleDe_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(titleDe: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.titleDe'));
    }

    /** @test */
    public function update_missingTitleEn_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(titleEn: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.titleEn'));
    }

    /** @test */
    public function update_missingDescriptionDE_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(descriptionDe: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.descriptionDe'));
    }

    /** @test */
    public function update_missingDescriptionEn_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(descriptionEn: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.descriptionEn'));
    }

    /** @test */
    public function update_missingEventLocation_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(eventLocationGuid: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.eventLocationGuid'));
    }

    /** @test */
    public function update_nonExistingEventLocation_returnsValidationError()
    {
        // Act
        $response = $this->updateRequest(eventLocationGuid: uuid_create());

        // Assert
        $response->assertStatus(404);
        $response->assertContent('not found');
    }

    /** @test */
    public function update_invalidTitleDeObject_returnsBadRequest()
    {
        // Act
        $response = $this->updateRequest(titleDe: ['bla' => 'bla']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors'));
        $this->assertCount(1, $response->json('errors.titleDe'));
    }

    /** @test */
    public function update_validEvent_eventIsStoredInDb()
    {
        // Arrange
        $eventData = static::getTestEventData();

        // Act
        $response = $this->updateRequest();

        // Assert
        $response->assertStatus(200);
        $allEvents = SingleEvent::all();
        $this->assertCount(1, $allEvents);
        /** @var SingleEvent $newEvent */
        $newEvent = $allEvents[0];
        /** @var EventLocation $newEventLocation */
        $newEventLocation = $newEvent->eventLocation()->first();
        $this->assertEquals($eventData['titleDe'], $newEvent->title_de);
        $this->assertEquals($eventData['titleEn'], $newEvent->title_en);
        $this->assertEquals($eventData['descriptionDe'], $newEvent->description_de);
        $this->assertEquals($eventData['descriptionEn'], $newEvent->description_en);
        $this->assertEquals(new Carbon($eventData['start']), $newEvent->start);
        $this->assertEquals(new Carbon($eventData['end']), $newEvent->end);
        $this->assertEquals($this->eventLocation->name, $newEventLocation->name);
        $this->assertEquals($this->eventLocation->street, $newEventLocation->street);
        $this->assertEquals($this->eventLocation->city, $newEventLocation->city);
    }

    /** @test */
    public function update_validEvent_updatedEventIsReturned()
    {
        // Arrange
        $eventData = static::getTestEventData();

        // Act
        $response = $this->updateRequest();

        // Assert
        $response->assertStatus(200);
        $response->assertJson(fn(AssertableJson $json) => $json->where('titleDe', $eventData['titleDe'])
            ->where('guid', fn(mixed $guid) => is_string($guid))
            ->where('titleEn', $eventData['titleEn'])
            ->where('descriptionDe', $eventData['descriptionDe'])
            ->where('descriptionEn', $eventData['descriptionEn'])
            ->where('start', fn(string $start) => new Carbon($start) == new Carbon($eventData['start']))
            ->where('end', fn(string $end) => new Carbon($end) == new Carbon($eventData['end']))
            ->where('eventLocation.name', $this->eventLocation->name)
            ->where('eventLocation.street', $this->eventLocation->street)
            ->where('eventLocation.city', $this->eventLocation->city)
            ->where('eventLocation.additionalInformation', $this->eventLocation->additional_information)
            ->where('image.fileUrl', 'http://localhost:8000/storage/' . $this->fileUpload->file_path)
            ->where('image.mimeType', 'image/png')
            ->where('isPublic', false)
        );
    }

    /** @test */
    public function update_userIsNotAuthorized_returnsForbidden()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->updateRequest(user: $user);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function delete_notAuthenticated_returnsUnauthenticated()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->delete("$this->basePath/$oldEvent->guid", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function delete_notAuthorized_returnsUnauthorized()
    {
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
    public function delete_unknownEvent_returnsNotFound()
    {
        // Act
        $response = $this->deleteRequest('unknown');

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function delete_allValid_eventIsDeleted()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->deleteRequest($oldEvent->guid);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals(0, SingleEvent::query()->where('guid', $oldEvent->guid)->count());
    }

    /** @test */
    public function publish_notAuthenticated_returnsUnauthenticated()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->post("$this->basePath/$oldEvent->guid/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function publish_notAuthorized_returnsUnauthorized()
    {
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
    public function publish_unknownEvent_returnsNotFound()
    {
        // Act
        $response = $this->actingAs($this->user)->post("$this->basePath/unknown/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function publish_allValid_eventIsPublished()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create(['is_public' => false]);

        // Act
        $response = $this->actingAs($this->user)->post("$this->basePath/$oldEvent->guid/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEvent $updatedEvent */
        $updatedEvent = SingleEvent::query()->where('guid', $oldEvent->guid)->first();
        $this->assertTrue($updatedEvent->is_public);
    }

    /** @test */
    public function unpublish_notAuthenticated_returnsUnauthenticated()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create();

        // Act
        $response = $this->post("$this->basePath/$oldEvent->guid/unpublish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function unpublish_notAuthorized_returnsUnauthorized()
    {
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
    public function unpublish_unknownEvent_returnsNotFound()
    {
        // Act
        $response = $this->actingAs($this->user)->post("$this->basePath/unknown/unpublish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function unpublish_allValid_eventIsUnpublished()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createSingleEvent()->create(['is_public' => true]);

        // Act
        $response = $this->actingAs($this->user)->post("$this->basePath/$oldEvent->guid/unpublish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEvent $updatedEvent */
        $updatedEvent = SingleEvent::query()->where('guid', $oldEvent->guid)->first();
        $this->assertFalse($updatedEvent->is_public);
    }

    private static function getTestEventData(): array
    {
        return [
            'titleDe' => 'test title de',
            'titleEn' => 'test title en',
            'descriptionDe' => 'test description de',
            'descriptionEn' => 'test description en',
            'eventLocationName' => 'test eventLocation name',
            'eventLocationStreet' => 'test eventLocation street',
            'eventLocationCity' => 'test eventLocation city',
            'start' => '2024-01-06T16:30:00.0Z',
            'end' => '2024-01-06T18:30:00.0Z'
        ];
    }

    private static function createSingleEvent(
        ?EventLocation $eventLocation = null,
        ?User          $user = null,
        ?FileUpload    $fileUpload = null
    ): Factory
    {
        $eventLocation = !is_null($eventLocation) ? $eventLocation : EventLocation::factory()->create();
        $user = !is_null($user) ? $user : User::factory()->create();
        $fileUpload = !is_null($fileUpload) ? $fileUpload : FileUpload::factory()->for($user, 'uploadedBy')->create();
        return SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy');
    }

    private function listRequest(?string $start = null, ?string $end = null): TestResponse
    {
        if (!is_null($start) && !is_null($end)) {
            return $this->get("/api/singleEvent/list?start=$start&end=$end", ['Accept' => 'application/json']);
        }
        if (!is_null($start)) {
            return $this->get("/api/singleEvent/list?start=$start", ['Accept' => 'application/json']);
        }
        if (!is_null($end)) {
            return $this->get("/api/singleEvent/list?end=$end", ['Accept' => 'application/json']);
        }
        return $this->get("$this->basePath/list", ['Accept' => 'application/json']);
    }

    private function createRequest(
        ?User   $user = null,
        mixed   $titleDe = null,
        ?string $titleEn = null,
        ?string $descriptionDe = null,
        ?string $descriptionEn = null,
        ?string $start = null,
        ?string $end = null,
        ?string $eventLocationGuid = null,
        ?string $fileUploadGuid = null,
    ): TestResponse
    {
        $sample = static::getTestEventData();
        return $this->actingAs($user ?? $this->user)->post("$this->basePath", [
            'titleDe' => $titleDe ?? $sample['titleDe'],
            'titleEn' => $titleEn ?? $sample['titleEn'],
            'descriptionDe' => $descriptionDe ?? $sample['descriptionDe'],
            'descriptionEn' => $descriptionEn ?? $sample['descriptionEn'],
            'eventLocationGuid' => $eventLocationGuid ?? $this->eventLocation->guid,
            'fileUploadGuid' => $fileUploadGuid ?? $this->fileUpload->guid,
            'start' => $start ?? $sample['start'],
            'end' => $end ?? $sample['end'],
        ], ['Accept' => 'application/json']);
    }

    private function createRequestWithMissing(
        bool $titleDe = false,
        bool $titleEn = false,
        bool $descriptionEn = false,
        bool $descriptionDe = false,
        bool $start = false,
        bool $end = false,
        bool $eventLocationGuid = false,
        bool $fileUploadGuid = false,
    ): TestResponse
    {
        $sample = static::getTestEventData();
        return $this->actingAs($user ?? $this->user)->post("$this->basePath", [
            'titleDe' => $titleDe ? null : $sample['titleDe'],
            'titleEn' => $titleEn ? null : $sample['titleEn'],
            'descriptionDe' => $descriptionDe ? null : $sample['descriptionDe'],
            'descriptionEn' => $descriptionEn ? null : $sample['descriptionEn'],
            'eventLocationGuid' => $eventLocationGuid ? null : $this->eventLocation->guid,
            'fileUploadGuid' => $fileUploadGuid ? null : $this->fileUpload->guid,
            'start' => $start ? null : $sample['start'],
            'end' => $end ? null : $sample['end'],
        ], ['Accept' => 'application/json']);
    }

    private function updateRequest(
        ?string $oldEventGuid = null,
        ?User   $user = null,
        mixed   $titleDe = null,
        ?string $titleEn = null,
        ?string $descriptionDe = null,
        ?string $descriptionEn = null,
        ?string $start = null,
        ?string $end = null,
        ?string $eventLocationGuid = null,
        ?string $fileUploadGuid = null,
    ): TestResponse
    {
        if (is_null($oldEventGuid)) {
            $oldEvent = SingleEvent::factory()
                ->for($this->eventLocation)
                ->for($this->fileUpload)
                ->for($this->user, 'createdBy')
                ->create();
            $oldEventGuid = $oldEvent->guid;
        }
        $sample = static::getTestEventData();
        return $this->actingAs($user ?? $this->user)->post("$this->basePath/$oldEventGuid/update", [
            'titleDe' => $titleDe ?? $sample['titleDe'],
            'titleEn' => $titleEn ?? $sample['titleEn'],
            'descriptionDe' => $descriptionDe ?? $sample['descriptionDe'],
            'descriptionEn' => $descriptionEn ?? $sample['descriptionEn'],
            'eventLocationGuid' => $eventLocationGuid ?? $this->eventLocation->guid,
            'fileUploadGuid' => $fileUploadGuid ?? $this->fileUpload->guid,
            'start' => $start ?? $sample['start'],
            'end' => $end ?? $sample['end'],
        ], ['Accept' => 'application/json']);
    }

    private function updateRequestWithMissing(
        ?string $oldEventGuid = null,
        bool    $titleDe = false,
        bool    $titleEn = false,
        bool    $descriptionEn = false,
        bool    $descriptionDe = false,
        bool    $start = false,
        bool    $end = false,
        bool    $eventLocationGuid = false,
        bool    $fileUploadGuid = false,
    ): TestResponse
    {
        if (is_null($oldEventGuid)) {
            $oldEvent = SingleEvent::factory()
                ->for($this->eventLocation)
                ->for($this->fileUpload)
                ->for($this->user, 'createdBy')
                ->create();
            $oldEventGuid = $oldEvent->guid;
        }
        $sample = static::getTestEventData();
        return $this->actingAs($user ?? $this->user)->post("$this->basePath/$oldEventGuid/update", [
            'titleDe' => $titleDe ? null : $sample['titleDe'],
            'titleEn' => $titleEn ? null : $sample['titleEn'],
            'descriptionDe' => $descriptionDe ? null : $sample['descriptionDe'],
            'descriptionEn' => $descriptionEn ? null : $sample['descriptionEn'],
            'eventLocationGuid' => $eventLocationGuid ? null : $this->eventLocation->guid,
            'fileUploadGuid' => $fileUploadGuid ? null : $this->fileUpload->guid,
            'start' => $start ? null : $sample['start'],
            'end' => $end ? null : $sample['end'],
        ], ['Accept' => 'application/json']);
    }

    public function deleteRequest(string|null $oldEventGuid = null): TestResponse
    {
        if (is_null($oldEventGuid)) {
            /** @var SingleEvent $oldEvent */
            $oldEvent = static::createSingleEvent()->create();
            $oldEventGuid = $oldEvent->guid;
        }


        return $this->actingAs($this->user)->delete("$this->basePath/$oldEventGuid", [], ['Accept' => 'application/json']);
    }
}
