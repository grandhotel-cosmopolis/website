<?php

namespace Tests\Feature\Controller;

use App\Http\Controllers\Event\SingleEventDto;
use App\Models\EventLocation;
use App\Models\SingleEvent;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SingleEventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_getSingleEvents_default_call_returnsAllSingleEvents() {

        // Arrange
        /** @var SingleEvent $createdEvent */
        $createdEvent = SingleEvent::factory()->for(EventLocation::factory()->create())->create([
            'start' => Carbon::now()->addDays(5),
            'end' => Carbon::now()->addDays(5)->addHours(2)
        ]);

        // Act
        $response = $this->get('/api/singleEvents/list', ['Accept', 'application/json']);

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
        $this->assertEquals($createdEvent->imageUrl, $event['image_url']);
    }

    public function test_getSingleEvents_noEventsInNext3Weeks_returnsEmptyList () {
        // Arrange
        /** @var SingleEvent $createdEvent */
        SingleEvent::factory()->for(EventLocation::factory()->create())->create([
            'start' => Carbon::now()->addMonth(),
            'end' => Carbon::now()->addMonth()->addHours(2)
        ]);

        // Act
        $response = $this->get('/api/singleEvents/list', ['Accept', 'application/json']);
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
        SingleEvent::factory()->for($eventLocation)->create([
            'start' => new Carbon('2024-01-06T16:30:00.000Z'),
            'end' => new Carbon('2024-01-06T18:00:00.000Z')
        ]);
        SingleEvent::factory()->for($eventLocation)->create([
            'start' => new Carbon('2024-01-04T16:30:00.000Z'),
            'end' => new Carbon('2024-01-06T18:00:00.000Z')
        ]);
        SingleEvent::factory()->for($eventLocation)->create([
            'start' => new Carbon('2024-01-10T16:30:00.000Z'),
            'end' => new Carbon('2024-01-12T18:00:00.000Z')
        ]);
        // Event before requested time range
        SingleEvent::factory()->for($eventLocation)->create([
            'start' => new Carbon('2024-01-05T16:30:00.000Z'),
            'end' => new Carbon('2024-01-05T18:00:00.000Z')
        ]);
        // Event after requested time range
        SingleEvent::factory()->for($eventLocation)->create([
            'start' => new Carbon('2024-01-11T16:30:00.000Z'),
            'end' => new Carbon('2024-01-11T18:00:00.000Z')
        ]);

        // Act
        $response = $this->get("/api/singleEvents/list?start=$start&end=$end", ['Accept', 'application/json']);

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
        $response = $this->get("/api/singleEvents/list?start=$start", ['Accept', 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    public function test_getSingleEvents_onlyEnd_returnsBadRequest() {
        // Arrange
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvents/list?end=$end", ['Accept', 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    public function test_getSingleEvents_startIsAfterEnd_returnsBadRequest() {
        // Arrange
        $start = '2024-02-06T16:34:42.511Z';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvents/list?start=$start&end=$end", ['Accept', 'application/json']);

        // Assert
        $response->assertStatus(400);
    }

    public function test_getSingleEvents_startIsNotParsable_returnsBadRequest() {
        // Arrange
        $start = 'unparsable';
        $end = '2024-01-06T16:34:42.511Z';

        // Act
        $response = $this->get("/api/singleEvents/list?start=$start&end=$end", ['Accept', 'application/json']);

        // Assert
        $response->assertStatus(400);
    }
}
