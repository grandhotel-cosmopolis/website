<?php

namespace Tests\Feature\Controller;

use App\Http\Controllers\Event\Recurrence;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\SingleEvent;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RecurringEventControllerTest extends TestCase
{
    private string $basePath = "/api/recurringEvent";

    public function test_addRecurringEvent_notLoggedIn_ReturnsUnauthenticated() {
        // Act
        $response = $this->post("$this->basePath/add", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    public function test_addRecurringEvent_invalidDataMissingTitleDe_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.default_title_de'));
    }

    public function test_addRecurringEvent_invalidDataMissingTitleEn_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.default_title_en'));
    }

    public function test_addRecurringEvent_invalidDataMissingDescriptionDe_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.default_description_de'));
    }

    public function test_addRecurringEvent_invalidDataMissingDescriptionEn_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.default_description_en'));
    }

    public function test_addRecurringEvent_invalidDataMissingStart_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.start_first_occurrence'));
    }

    public function test_addRecurringEvent_invalidDataMissingEnd_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.end_first_occurrence'));
    }

    public function test_addRecurringEvent_invalidDataMissingRecurrence_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrence'));
    }

    public function test_addRecurringEvent_invalidDataMissingRecurrenceMetadata_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrence_metadata'));
    }

    public function test_addRecurringEvent_invalidDataMissingEventLocation_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.default_event_location_guid'));
    }

    public function test_addRecurringEvent_invalidDataMissingFileUpload_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.default_file_upload_guid'));
    }

    public function test_addRecurringEvent_invalidDataInvalidRecurrence_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => 'invalid',
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrence'));
    }

    public function test_addRecurringEvent_invalidDataStartNotParsable_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => 'invalid',
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.start_first_occurrence'));
    }

    public function test_addRecurringEvent_invalidDataEndFirstNotParsable_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => 'invalid',
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.end_first_occurrence'));
    }

    public function test_addRecurringEvent_invalidDataEndNotParsable_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => $sample['start_first_occurrence'],
            'end_first_occurrence' => $sample['end_first_occurrence'],
            'end_recurrence' => 'invalid',
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.end_recurrence'));
    }

    public function test_addRecurringEvent_invalidDataStartAfterEnd_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T14:00:00.0Z',
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertContent('invalid time range');
    }

    public function test_addRecurringEvent_invalidDataEndBeforeFirstStart_ReturnsValidationError() {
        // Arrange
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $sample['default_title_de'],
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'end_recurrence' => '2024-01-05T18:00:00.0Z',
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(422);
        $response->assertContent('invalid end_recurrence date');
    }

    public function test_addRecurringEvent_validData_eventsAreCreated() {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        $titleDe = $sample['default_title_de'] . uuid_create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $titleDe,
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()
            ->where('default_title_de', $titleDe)
            ->get()
            ->first();

        $this->assertEquals($titleDe, $recurringEvent->default_title_de);
        $this->assertEquals($sample['default_title_en'], $recurringEvent->default_title_en);
        $this->assertEquals($sample['default_description_de'], $recurringEvent->default_description_de);
        $this->assertEquals($sample['default_description_en'], $recurringEvent->default_description_en);
        $this->assertEquals($sample['recurrence'], $recurringEvent->recurrence->value);
        $this->assertEquals($sample['recurrence_metadata'], $recurringEvent->recurrence_metadata);
        $this->assertEquals($eventLocation->guid, $recurringEvent->defaultEventLocation()->get()->first()->guid);
        $this->assertEquals($fileUpload->guid, $recurringEvent->defaultFileUpload()->get()->first()->guid);

        /** @var SingleEvent[] $singleEvents */
        $singleEvents = $recurringEvent->singleEvents()->get();

        foreach ($singleEvents as $singleEvent) {
            $this->assertEquals($titleDe, $singleEvent->title_de);
            $this->assertEquals($sample['default_title_en'], $singleEvent->title_en);
            $this->assertEquals($sample['default_description_de'], $singleEvent->description_de);
            $this->assertEquals($sample['default_description_en'], $singleEvent->description_en);
            $this->assertTrue($singleEvent->is_recurring);
            $this->assertEquals($eventLocation->guid, $singleEvent->eventLocation()->get()->first()->guid);
            $this->assertEquals($fileUpload->guid, $singleEvent->fileUpload()->get()->first()->guid);
        }

    }

    public function test_addRecurringEvent_validData_recurringEventIsReturned()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        $titleDe = $sample['default_title_de'] . uuid_create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $titleDe,
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => $sample['recurrence'],
            'recurrence_metadata' => $sample['recurrence_metadata']
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('title_de', $titleDe)
                ->where('title_en', $sample['default_title_en'])
                ->where('description_de', $sample['default_description_de'])
                ->where('description_en', $sample['default_description_en'])
                ->where("eventLocation.name", $eventLocation->name)
                ->where('eventLocation.street', $eventLocation->street)
                ->where('eventLocation.city', $eventLocation->city)
                ->where('image.fileUrl', 'http://localhost:8000/storage/' . $fileUpload->file_path)
                ->where('image.mimeType', 'image/png')
                ->where('recurrence', $sample['recurrence'])
                ->where('recurrenceMetadata', $sample['recurrence_metadata'])
                ->etc());
    }

    public function test_addRecurringEvent_everyMonthAtDayX_correctEventsAreCreated() {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        $titleDe = $sample['default_title_de'] . uuid_create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $titleDe,
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => Recurrence::EVERY_MONTH_AT_DAY_X->value,
            'recurrence_metadata' => 10
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('default_title_de', $titleDe)->get()->first();

        /** @var SingleEvent[] $singleEVents*/
        $singleEVents = $recurringEvent->singleEvents()->get();

        /** @var int[] $months */
        $months = [];

        foreach ($singleEVents as $singleEvent) {
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->hour, Carbon::parse($singleEvent->start)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            $this->assertEquals(Carbon::parse($recurringEvent->end_first_occurrence)->hour, Carbon::parse($singleEvent->end)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(10, Carbon::parse($singleEvent->start)->day);
                $this->assertEquals(10, Carbon::parse($singleEvent->end)->day);
            }
            $months[] = Carbon::parse($singleEvent->start)->month;
        }

        $this->assertSameSize($months, array_unique($months));
    }

    public function test_addRecurringEvent_everyMonthAtDayXEndOfMonth_correctAmountOfEventsAreCreated() {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        $titleDe = $sample['default_title_de'] . uuid_create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $titleDe,
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => Recurrence::EVERY_MONTH_AT_DAY_X->value,
            'recurrence_metadata' => 31
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('default_title_de', $titleDe)->get()->first();

        /** @var SingleEvent[] $singleEVents*/
        $singleEVents = $recurringEvent->singleEvents()->get();

        /** @var int[] $months */
        $months = [];

        foreach ($singleEVents as $singleEvent) {
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->hour, Carbon::parse($singleEvent->start)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            $this->assertEquals(Carbon::parse($recurringEvent->end_first_occurrence)->hour, Carbon::parse($singleEvent->end)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertGreaterThan(28, Carbon::parse($singleEvent->start)->day);
                $this->assertGreaterThan(28, Carbon::parse($singleEvent->end)->day);
            }
            $months[] = Carbon::parse($singleEvent->start)->month;
        }

        $this->assertSameSize($months, array_unique($months));
    }

    public function test_addRecurringEvent_everyLastDayInMonth_correctAmountOfEventsAreCreated() {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        $titleDe = $sample['default_title_de'] . uuid_create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $titleDe,
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => Recurrence::EVERY_LAST_DAY_IN_MONTH->value,
            'recurrence_metadata' => 0
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('default_title_de', $titleDe)->get()->first();

        /** @var SingleEvent[] $singleEVents*/
        $singleEVents = $recurringEvent->singleEvents()->get();

        /** @var int[] $months */
        $months = [];

        foreach ($singleEVents as $singleEvent) {
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->hour, Carbon::parse($singleEvent->start)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            $this->assertEquals(Carbon::parse($recurringEvent->end_first_occurrence)->hour, Carbon::parse($singleEvent->end)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertGreaterThan(21, Carbon::parse($singleEvent->start)->day);
                $this->assertGreaterThan(21, Carbon::parse($singleEvent->end)->day);
            }
            $months[] = Carbon::parse($singleEvent->start)->month;
        }

        $this->assertSameSize($months, array_unique($months));
    }

    public function test_addRecurringEvent_everyFirstDayInMonth_correctAmountOfEventsAreCreated() {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        $titleDe = $sample['default_title_de'] . uuid_create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $titleDe,
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => Recurrence::EVERY_FIRST_DAY_IN_MONTH->value,
            'recurrence_metadata' => 3
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('default_title_de', $titleDe)->get()->first();

        /** @var SingleEvent[] $singleEVents*/
        $singleEVents = $recurringEvent->singleEvents()->get();

        /** @var int[] $months */
        $months = [];

        foreach ($singleEVents as $singleEvent) {
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->hour, Carbon::parse($singleEvent->start)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            $this->assertEquals(Carbon::parse($recurringEvent->end_first_occurrence)->hour, Carbon::parse($singleEvent->end)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::WEDNESDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::WEDNESDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
            $months[] = Carbon::parse($singleEvent->start)->month;
        }

        $this->assertSameSize($months, array_unique($months));
    }

    public function test_addRecurringEvent_everySecondDayInMonth_correctAmountOfEventsAreCreated() {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        $titleDe = $sample['default_title_de'] . uuid_create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $titleDe,
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => Recurrence::EVERY_SECOND_DAY_IN_MONTH->value,
            'recurrence_metadata' => 5
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('default_title_de', $titleDe)->get()->first();

        /** @var SingleEvent[] $singleEVents*/
        $singleEVents = $recurringEvent->singleEvents()->get();

        /** @var int[] $months */
        $months = [];

        foreach ($singleEVents as $singleEvent) {
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->hour, Carbon::parse($singleEvent->start)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            $this->assertEquals(Carbon::parse($recurringEvent->end_first_occurrence)->hour, Carbon::parse($singleEvent->end)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::FRIDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::FRIDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
            $months[] = Carbon::parse($singleEvent->start)->month;
        }

        $this->assertSameSize($months, array_unique($months));
    }

    public function test_addRecurringEvent_everyThirdDayInMonth_correctEventsAreCreated() {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $user = User::factory()->create();
        $sample = static::getTestEventData();
        $eventLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();

        $titleDe = $sample['default_title_de'] . uuid_create();

        // Act
        $response = $this->actingAs($user)->post("$this->basePath/add", [
            'default_title_en' => $sample['default_title_en'],
            'default_title_de' => $titleDe,
            'default_description_de' => $sample['default_description_de'],
            'default_description_en' => $sample['default_description_en'],
            'default_event_location_guid' => $eventLocation->guid,
            'default_file_upload_guid' => $fileUpload->guid,
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => Recurrence::EVERY_THIRD_DAY_IN_MONTH->value,
            'recurrence_metadata' => 6
        ], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('default_title_de', $titleDe)->get()->first();

        /** @var SingleEvent[] $singleEVents*/
        $singleEVents = $recurringEvent->singleEvents()->get();

        /** @var int[] $months */
        $months = [];

        foreach ($singleEVents as $singleEvent) {
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->hour, Carbon::parse($singleEvent->start)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            $this->assertEquals(Carbon::parse($recurringEvent->end_first_occurrence)->hour, Carbon::parse($singleEvent->end)->hour);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->minute, Carbon::parse($singleEvent->start)->minute);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->second, Carbon::parse($singleEvent->start)->second);
            $this->assertEquals(Carbon::parse($recurringEvent->start_first_occurrence)->millisecond, Carbon::parse($singleEvent->start)->millisecond);

            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::SATURDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::SATURDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
            $months[] = Carbon::parse($singleEvent->start)->month;
        }

        $this->assertSameSize($months, array_unique($months));
    }

    private static function getTestEventData(): array {
        return [
            'default_title_de' => 'test title de',
            'default_title_en' => 'test title en',
            'default_description_de' => 'test description de',
            'default_description_en' => 'test description en',
            'start_first_occurrence' => '2024-01-06T16:30:00.0Z',
            'end_first_occurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => Recurrence::EVERY_X_DAYS->value,
            'recurrence_metadata' => 31
        ];
    }
}
