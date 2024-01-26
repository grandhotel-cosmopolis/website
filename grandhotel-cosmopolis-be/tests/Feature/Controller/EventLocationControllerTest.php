<?php

namespace Tests\Feature\Controller;

use App\Models\EventLocation;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class EventLocationControllerTest extends TestCase
{
    private string $basePath = "/api/eventLocation";

    public function test_list_notLoggedIn_returnsUnauthenticated () {
        // Act
        $response = $this->get("$this->basePath/list", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    public function test_list_authenticated_returnsAllEventLocations () {
        // Arrange
        EventLocation::factory()->count(10)->create();
        $countEventLocations = EventLocation::query()->count();
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get("$this->basePath/list", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertCount($countEventLocations, $response->json("eventLocations"));
    }

    public function test_addEventLocation_unauthenticated_returnsUnauthenticated() {
        // Act
        $response = $this->post("$this->basePath/add", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    public function test_addEventLocation_noName_returnsValidationError() {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.name'));
    }

    public function test_addEventLocation_valid_eventLocationIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $eventLocation = EventLocation::factory()->make();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath/add",
            [
                'name' => $eventLocation->name,
                'street' => $eventLocation->street,
                'city' => $eventLocation->city
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(200);

        /** @var EventLocation[] $savedEventLocation */
        $savedEventLocation = EventLocation::query()
            ->where('name', $eventLocation->name)
            ->where('street', $eventLocation->street)
            ->where('city', $eventLocation->city)
            ->get();

        $this->assertCount(1, $savedEventLocation);
        $this->assertNotEquals($savedEventLocation[0]->guid, $eventLocation->guid);
    }

    public function test_addEventLocation_onlyNameGiven_eventLocationIsStoredInDb() {
        // Arrange
        $user = User::factory()->create();
        $eventLocationName = "TEST Event Location" . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath/add",
            [
                'name' => $eventLocationName
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(200);

        /** @var EventLocation[] $savedEventLocation */
        $savedEventLocation = EventLocation::query()
            ->where('name', $eventLocationName)
            ->get();

        $this->assertCount(1, $savedEventLocation);
        $this->assertNull($savedEventLocation[0]['street']);
        $this->assertNull($savedEventLocation[0]['city']);
    }

    public function test_addEventLocation_allValid_newEventLocationIsReturned() {
        // Arrange
        $user = User::factory()->create();
        $eventLocationName = "TEST Event Location " . uuid_create();
        $eventLocationStreet = "Test street " . uuid_create();
        $eventLocationCity = "Test City " . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath/add",
            [
                'name' => $eventLocationName,
                'street' => $eventLocationStreet,
                'city' => $eventLocationCity
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('name', $eventLocationName)
                ->where('street', $eventLocationStreet)
                ->where('city', $eventLocationCity)
                ->etc()
        );
    }
}
