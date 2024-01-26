<?php

namespace Tests\Feature\Controller;

use App\Http\Dtos\Event\SingleEventDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SingleEventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_getSingleEvents_default_call_returnsAllSingleEvents() {

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

    public function test_getSingleEvents_noEventsInNext3Weeks_returnsEmptyList () {
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

    public function test_getSingleEvents_specifiedStartAndEnd_returnsOnlyEventsInRange() {
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

    public function test_getSingleEvents_onlyStart_returnsBadRequest() {
        // Arrange
        $start = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvent/list?start=$start", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    public function test_getSingleEvents_onlyEnd_returnsBadRequest() {
        // Arrange
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvent/list?end=$end", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    public function test_getSingleEvents_startIsAfterEnd_returnsBadRequest() {
        // Arrange
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvent/list?start=$start&end=$end", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    public function test_getSingleEvents_startIsNotParsable_returnsBadRequest() {
        // Arrange
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvent/list?start=$start&end=$end", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    public function test_addSingleEvent_notLoggedIn_returnsUnauthenticated() {
        // Act
        $response = $this->post('/api/singleEvent/add', [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    public function test_addSingleEvent_notParsableTime_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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

    public function test_addSingleEvent_startIsAfterEnd_returnsValidationError() {
        // Arrange
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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
        $response->assertContent('invalid time range');
    }

    public function test_addSingleEvent_onlyEnd_returnsValidationError() {
        // Arrange
        $end = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->post('/api/singleEvent/add', ['end' => $end], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    public function test_addSingleEvent_onlyStart_returnsValidationError() {
        // Arrange
        $start = '2024-01-06T16:34:42.511Z';
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->post('/api/singleEvent/add', ['start' => $start], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
    }

    public function test_addSingleEvent_missingTitleDe_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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

    public function test_addSingleEvent_missingTitleEn_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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

    public function test_addSingleEvent_missingDescriptionDE_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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

    public function test_addSingleEvent_missingDescriptionEN_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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

    public function test_addSingleEvent_missingEventLocation_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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

    public function test_addSingleEvent_nonExistingEventLocation_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $nonExistingEventLocationGuid = uuid_create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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
        $response->assertStatus(422);
        $response->assertContent('invalid input');
    }

    public function test_addSingleEvent_invalidTitleDeObject_returnsBadRequest() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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

    public function test_addSingleEvent_validEvent_eventIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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

    public function test_addSingleEvent_validEvent_createdEventIsReturned() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $eventData = static::getTestEventData();

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/singleEvent/add', [
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
