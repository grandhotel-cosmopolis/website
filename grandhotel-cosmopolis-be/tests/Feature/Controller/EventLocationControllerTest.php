<?php

namespace Tests\Feature\Controller;

use App\Models\EventLocation;
use App\Models\Permissions;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class EventLocationControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $basePath = "/api/eventLocation";

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /** @test */
    public function list_notLoggedIn_returnsUnauthenticated()
    {
        // Act
        $response = $this->get("$this->basePath/list", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function list_notAuthorized_returnsUnauthorized()
    {
        // Arrange
        EventLocation::factory()->count(10)->create();
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get("$this->basePath/list", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function list_authenticated_returnsAllEventLocations()
    {
        // Arrange
        EventLocation::factory()->count(10)->create();
        $countEventLocations = EventLocation::query()->count();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT->value);

        // Act
        $response = $this->actingAs($user)->get("$this->basePath/list", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertCount($countEventLocations, $response->json("eventLocations"));
    }

    /** @test */
    public function create_unauthenticated_returnsUnauthenticated()
    {
        // Act
        $response = $this->post("$this->basePath", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function create_noName_returnsValidationError()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT->value);

        // Act
        $response = $this->actingAs($user)->post("$this->basePath", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.name'));
    }

    /** @test */
    public function create_valid_eventLocationIsStoredInDb()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT->value);
        $eventLocation = EventLocation::factory()->make();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath",
            [
                'name' => $eventLocation->name,
                'street' => $eventLocation->street,
                'city' => $eventLocation->city,
                'additionalInformation' => $eventLocation->additional_information
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
            ->where('additional_information', $eventLocation->additional_information)
            ->get();

        $this->assertCount(1, $savedEventLocation);
        $this->assertNotEquals($savedEventLocation[0]->guid, $eventLocation->guid);
    }

    /** @test */
    public function create_onlyNameGiven_eventLocationIsStoredInDb()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT->value);
        $eventLocationName = "TEST Event Location" . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath",
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
        $this->assertNull($savedEventLocation[0]['additional_information']);
    }

    /** @test */
    public function create_allValid_newEventLocationIsReturned()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT->value);
        $eventLocationName = "TEST Event Location " . uuid_create();
        $eventLocationStreet = "Test street " . uuid_create();
        $eventLocationCity = "Test City " . uuid_create();
        $eventLocationAdditionalInformation = "Test infos " . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath",
            [
                'name' => $eventLocationName,
                'street' => $eventLocationStreet,
                'city' => $eventLocationCity,
                'additionalInformation' => $eventLocationAdditionalInformation
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(200);
        $response->assertJson(fn(AssertableJson $json) => $json->where('name', $eventLocationName)
            ->where('street', $eventLocationStreet)
            ->where('city', $eventLocationCity)
            ->where('additionalInformation', $eventLocationAdditionalInformation)
            ->etc()
        );
    }

    /** @test */
    public function create_notAuthorized_returnsUnauthorized()
    {
        // Arrange
        $user = User::factory()->create();
        $eventLocationName = "TEST Event Location " . uuid_create();
        $eventLocationStreet = "Test street " . uuid_create();
        $eventLocationCity = "Test City " . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath",
            [
                'name' => $eventLocationName,
                'street' => $eventLocationStreet,
                'city' => $eventLocationCity
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(403);
        $eventLocations = EventLocation::query()->where('name', $eventLocationName)->get();
        $this->assertCount(0, $eventLocations);
    }

    /** @test */
    public function update_unauthenticated_returnsUnauthenticated()
    {
        // Arrange
        $eventLocation = EventLocation::factory()->create();

        // Act
        $response = $this->post("$this->basePath/$eventLocation->guid/update", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function update_noName_returnsValidationError()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT->value);
        $eventLocation = EventLocation::factory()->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/$eventLocation/update", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.name'));
    }

    /** @test */
    public function update_valid_eventLocationIsStoredInDb()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT->value);
        $oldEventLocation = EventLocation::factory()->create();
        $eventLocation = EventLocation::factory()->make();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath/$oldEventLocation->guid/update",
            [
                'name' => $eventLocation->name,
                'street' => $eventLocation->street,
                'city' => $eventLocation->city,
                'additionalInformation' => $eventLocation->additional_information
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(200);

        /** @var EventLocation[] $savedEventLocation */
        $savedEventLocation = EventLocation::query()
            ->where('guid', $oldEventLocation->guid)
            ->get();

        $this->assertCount(1, $savedEventLocation);
        $this->assertNotEquals($savedEventLocation[0]->guid, $eventLocation->guid);
    }

    /** @test */
    public function update_onlyNameGiven_eventLocationIsStoredInDb()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT->value);
        $oldEventLocation = EventLocation::factory()->create();
        $eventLocationName = "TEST Event Location" . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath/$oldEventLocation->guid/update",
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
        $this->assertNull($savedEventLocation[0]['additional_information']);
    }

    /** @test */
    public function update_allValid_updatedEventLocationIsReturned()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CREATE_EVENT->value);
        $eventLocationName = "TEST Event Location " . uuid_create();
        $eventLocationStreet = "Test street " . uuid_create();
        $eventLocationCity = "Test City " . uuid_create();
        $eventLocationExtraInfos = "Test Infos " . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath",
            [
                'name' => $eventLocationName,
                'street' => $eventLocationStreet,
                'city' => $eventLocationCity,
                'additionalInformation' => $eventLocationExtraInfos
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(200);
        $response->assertJson(fn(AssertableJson $json) => $json->where('name', $eventLocationName)
            ->where('street', $eventLocationStreet)
            ->where('city', $eventLocationCity)
            ->where('additionalInformation', $eventLocationExtraInfos)
            ->etc()
        );
    }

    /** @test */
    public function update_notAuthorized_returnsUnauthorized()
    {
        // Arrange
        $user = User::factory()->create();
        $oldEventLocation = EventLocation::factory()->create();
        $eventLocationName = "TEST Event Location " . uuid_create();
        $eventLocationStreet = "Test street " . uuid_create();
        $eventLocationCity = "Test City " . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath/$oldEventLocation->guid/update",
            [
                'name' => $eventLocationName,
                'street' => $eventLocationStreet,
                'city' => $eventLocationCity
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(403);
        $eventLocations = EventLocation::query()->where('name', $eventLocationName)->get();
        $this->assertCount(0, $eventLocations);
    }

    /** @test */
    public function update_notExistingEvent_returnsNotfound()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EDIT_EVENT->value);
        $eventLocationName = "TEST Event Location " . uuid_create();
        $eventLocationStreet = "Test street " . uuid_create();
        $eventLocationCity = "Test City " . uuid_create();

        // Act
        $response = $this->actingAs($user)->post(
            "$this->basePath/does-not-exist/update",
            [
                'name' => $eventLocationName,
                'street' => $eventLocationStreet,
                'city' => $eventLocationCity
            ],
            ['Accept' => 'application/json']
        );

        // Assert
        $response->assertStatus(404);
        $eventLocations = EventLocation::query()->where('name', $eventLocationName)->get();
        $this->assertCount(0, $eventLocations);
    }

    /** @test */
    public function delete_notAuthenticated_returnsUnauthenticated()
    {
        // Arrange
        $eventLocation = EventLocation::factory()->create();

        // Act
        $response = $this->delete("$this->basePath/$eventLocation->guid", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function delete_notAuthorized_returnsUnauthorized()
    {
        // Arrange
        $eventLocation = EventLocation::factory()->create();
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->delete("$this->basePath/$eventLocation->guid", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function delete_notExistingEvent_returnsNotFound()
    {
        // Arrange
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DELETE_EVENT);

        // Act
        $response = $this->actingAs($user)->delete("$this->basePath/not-existing", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function delete_valid_eventLocationIsDeleted()
    {
        // Arrange
        $eventLocation = EventLocation::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DELETE_EVENT);

        // Act
        $response = $this->actingAs($user)->delete("$this->basePath/$eventLocation->guid", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(0, EventLocation::query()->where('guid', $eventLocation->guid)->get());
    }
}
