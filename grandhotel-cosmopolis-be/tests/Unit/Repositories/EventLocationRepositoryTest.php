<?php

namespace Tests\Unit\Repositories;

use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\User;
use App\Repositories\EventLocationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class EventLocationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EventLocationRepository $cut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cut = new EventLocationRepository();
    }

    /** @test */
    public function create_allValid_eventLocationIsStoredInDb() {
        // Arrange
        $eventLocationData = EventLocation::factory()->make();

        // Act
        $this->cut->create($eventLocationData->name, $eventLocationData->street, $eventLocationData->city);

        // Assert
        /** @var EventLocation $storedEventLocation */
        $storedEventLocation = EventLocation::query()->where('name', $eventLocationData->name)->first();
        $this->assertNotNull($storedEventLocation->guid);
        $this->assertNotEquals($eventLocationData->guid, $storedEventLocation->guid);
        $this->assertEquals($eventLocationData->name, $storedEventLocation->name);
        $this->assertEquals($eventLocationData->street, $storedEventLocation->street);
        $this->assertEquals($eventLocationData->city, $storedEventLocation->city);
    }

    /** @test */
    public function create_allValid_createdEventLocationIsReturned() {
        // Arrange
        $eventLocationData = EventLocation::factory()->make();

        // Act
        $createdEventLocation = $this->cut->create($eventLocationData->name, $eventLocationData->street, $eventLocationData->city);

        // Assert
        $this->assertNotNull($createdEventLocation->guid);
        $this->assertNotEquals($eventLocationData->guid, $createdEventLocation->guid);
        $this->assertEquals($eventLocationData->name, $createdEventLocation->name);
        $this->assertEquals($eventLocationData->street, $createdEventLocation->street);
        $this->assertEquals($eventLocationData->city, $createdEventLocation->city);
    }

    /** @test */
    public function update_unknownEventLocation_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $data = EventLocation::factory()->make();

        // Act & Assert
        $this->cut->update('unknown', $data->name, $data->street, $data->city);
    }

    /** @test */
    public function update_allValid_eventLocationIsStoredInDb() {
        // Arrange
        $oldEventLocation = EventLocation::factory()->create();
        $eventLocationData = EventLocation::factory()->make();

        // Act
        $this->cut->update($oldEventLocation->guid, $eventLocationData->name, $eventLocationData->street, $eventLocationData->city);

        // Assert
        /** @var EventLocation $storedEventLocation */
        $storedEventLocation = EventLocation::query()->where('name', $eventLocationData->name)->first();
        $this->assertNotNull($storedEventLocation->guid);
        $this->assertNotEquals($eventLocationData->guid, $storedEventLocation->guid);
        $this->assertEquals($eventLocationData->name, $storedEventLocation->name);
        $this->assertEquals($eventLocationData->street, $storedEventLocation->street);
        $this->assertEquals($eventLocationData->city, $storedEventLocation->city);
    }

    /** @test */
    public function update_allValid_updatedEventLocationIsReturned() {
        // Arrange
        $oldEventLocation = EventLocation::factory()->create();
        $eventLocationData = EventLocation::factory()->make();

        // Act
        $updatedEventLocation = $this->cut->update($oldEventLocation->guid, $eventLocationData->name, $eventLocationData->street, $eventLocationData->city);

        // Assert
        $this->assertNotNull($updatedEventLocation->guid);
        $this->assertNotEquals($eventLocationData->guid, $updatedEventLocation->guid);
        $this->assertEquals($eventLocationData->name, $updatedEventLocation->name);
        $this->assertEquals($eventLocationData->street, $updatedEventLocation->street);
        $this->assertEquals($eventLocationData->city, $updatedEventLocation->city);
    }

    /** @test  */
    public function delete_unknownEventLocation_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        $this->cut->delete('unknown');
    }

    /** @test */
    public function delete_eventLocationIsUsed_throwsException() {
        // Arrange
        $this->expectException(UnprocessableEntityHttpException::class);
        $eventLocation = EventLocation::factory()->create();
        SingleEvent::factory()
            ->for($eventLocation)
            ->for(User::factory()->create(), 'createdBy')
            ->for(FileUpload::factory()->for(User::factory()->create(), 'uploadedBy'))
            ->create();

        // Act & Assert
        $this->cut->delete($eventLocation->guid);
    }

    /** @test */
    public function delete_allValid_eventLocationIsDeleted() {
        // Arrange
        $eventLocation = EventLocation::factory()->create();

        // Act
        $this->cut->delete($eventLocation->guid);

        // Assert
        $this->assertEquals(0, EventLocation::query()->where('guid', $eventLocation->guid)->count());
    }

    /** @test */
    public function list_allValid_allEventLocationsAreReturned() {
        // Arrange
        EventLocation::factory()->count(10)->create();

        // Act
        $eventLocations = $this->cut->list();

        // Assert
        $this->assertCount(10, $eventLocations);
    }
}
