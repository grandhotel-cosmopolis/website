<?php

namespace Tests\Feature\Controller;

use App\Http\Controllers\Event\Recurrence;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\Permissions;
use App\Models\RecurringEvent;
use App\Models\SingleEvent;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RecurringEventControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $basePath = "/api/recurringEvent";

    private User $user;
    private FileUpload $fileUpload;
    private EventLocation $eventLocation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            Permissions::CREATE_EVENT,
            Permissions::EDIT_EVENT,
            Permissions::DELETE_EVENT,
            Permissions::PUBLISH_EVENT,
            Permissions::UNPUBLISH_EVENT
        ]);
        $this->fileUpload = FileUpload::factory()->for($this->user, 'uploadedBy')->create();
        $this->eventLocation = EventLocation::factory()->create();
    }

    /** @test */
    public function create_notLoggedIn_returnsUnauthenticated()
    {
        // Act
        $response = $this->put("$this->basePath", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function create_notAuthorized_returnsUnauthorized()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->createRequest(user: $user);

        // Assert
        $response->assertStatus(403);
    }


    /** @test */
    public function create_invalidDataMissingTitleDe_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingTitleDe: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.titleDe'));
    }

    /** @test */
    public function create_invalidDataMissingTitleEn_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingTitleEn: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.titleEn'));
    }

    /** @test */
    public function create_invalidDataMissingDescriptionDe_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingDescriptionDe: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.descriptionDe'));
    }

    /** @test */
    public function create_invalidDataMissingDescriptionEn_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingDescriptionEn: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.descriptionEn'));
    }

    /** @test */
    public function create_invalidDataMissingStart_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingStartFirstOccurrence: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.startFirstOccurrence'));
    }

    /** @test */
    public function create_invalidDataMissingEnd_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingEndFirstOccurrence: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.endFirstOccurrence'));
    }

    /** @test */
    public function create_invalidDataMissingRecurrence_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingRecurrence: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrence'));
    }

    /** @test */
    public function create_invalidDataMissingRecurrenceMetadata_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingRecurrenceMetadata: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrenceMetadata'));
    }

    /** @test */
    public function create_invalidDataMissingEventLocation_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingEventLocationGuid: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.eventLocationGuid'));
    }

    /** @test */
    public function create_invalidDataMissingFileUpload_ReturnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingFileUploadGuid: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.fileUploadGuid'));
    }

    /** @test */
    public function create_invalidDataInvalidRecurrence_returnsValidationError()
    {
        // Act
        $response = $this->createRequest(recurrence: 'invalid');

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrence'));
    }

    /** @test */
    public function create_invalidDataStartNotParsable_returnsValidationError()
    {
        // Act
        $response = $this->createRequest(startFirstOccurrence: 'invalid');

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.startFirstOccurrence'));
    }

    /** @test */
    public function create_invalidDataEndFirstNotParsable_returnsValidationError()
    {
        // Act
        $response = $this->createRequest(endFirstOccurrence: 'invalid');

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.endFirstOccurrence'));
    }

    /** @test */
    public function create_invalidDataEndNotParsable_returnsValidationError()
    {
        // Act
        $response = $this->createRequest(endRecurrence: 'invalid');

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.endRecurrence'));
    }

    /** @test */
    public function create_invalidDataStartAfterEnd_returnsValidationError()
    {
        // Act
        $response = $this->createRequest(startFirstOccurrence: '2024-01-06T16:30:00.0Z', endFirstOccurrence: '2024-01-06T14:00:00.0Z');

        // Assert
        $response->assertStatus(400);
        $response->assertContent('invalid time range');
    }

    /** @test */
    public function create_invalidDataEndBeforeFirstStart_returnsValidationError()
    {
        // Act
        $response = $this->createRequest(
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            endRecurrence: '2024-01-05T18:00:00.0Z'
        );

        // Assert
        $response->assertStatus(400);
        $response->assertContent('invalid time range');
    }

    /** @test */
    public function create_validData_eventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();

        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->createRequest(titleDe: $titleDe);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()
            ->where('title_de', $titleDe)
            ->get()
            ->first();

        $this->assertEquals($titleDe, $recurringEvent->title_de);
        $this->assertEquals($sample['titleEn'], $recurringEvent->title_en);
        $this->assertEquals($sample['descriptionDe'], $recurringEvent->description_de);
        $this->assertEquals($sample['descriptionEn'], $recurringEvent->description_en);
        $this->assertEquals($sample['recurrence'], $recurringEvent->recurrence->value);
        $this->assertEquals($sample['recurrenceMetadata'], $recurringEvent->recurrence_metadata);
        $this->assertEquals($this->eventLocation->guid, $recurringEvent->eventLocation()->get()->first()->guid);
        $this->assertEquals($this->fileUpload->guid, $recurringEvent->fileUpload()->get()->first()->guid);

        /** @var SingleEvent[] $singleEvents */
        $singleEvents = $recurringEvent->singleEvents()->get();

        foreach ($singleEvents as $singleEvent) {
            $this->assertEquals($titleDe, $singleEvent->title_de);
            $this->assertEquals($sample['titleEn'], $singleEvent->title_en);
            $this->assertEquals($sample['descriptionDe'], $singleEvent->description_de);
            $this->assertEquals($sample['descriptionEn'], $singleEvent->description_en);
            $this->assertTrue($singleEvent->is_recurring);
            $this->assertEquals($this->eventLocation->guid, $singleEvent->eventLocation()->get()->first()->guid);
            $this->assertEquals($this->fileUpload->guid, $singleEvent->fileUpload()->get()->first()->guid);
        }

    }

    /** @test */
    public function create_validData_recurringEventIsReturned()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();

        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->createRequest(titleDe: $titleDe, startFirstOccurrence: '2024-01-06T16:30:00.0Z', endFirstOccurrence: '2024-01-06T18:00:00.0Z');

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());
        $response->assertJson(fn(AssertableJson $json) => $json->where('title_de', $titleDe)
            ->where('title_en', $sample['titleEn'])
            ->where('description_de', $sample['descriptionDe'])
            ->where('description_en', $sample['descriptionEn'])
            ->where("eventLocation.name", $this->eventLocation->name)
            ->where('eventLocation.street', $this->eventLocation->street)
            ->where('eventLocation.city', $this->eventLocation->city)
            ->where('image.fileUrl', 'http://localhost:8000/storage/' . $this->fileUpload->file_path)
            ->where('image.mimeType', 'image/png')
            ->where('recurrence', $sample['recurrence'])
            ->where('recurrenceMetadata', $sample['recurrenceMetadata'])
            ->etc());
    }

    /** @test */
    public function create_everyMonthAtDayX_correctEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();

        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->createRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_MONTH_AT_DAY_X->value,
            recurrenceMetadata: 10
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->get()->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(10, Carbon::parse($singleEvent->start)->day);
                $this->assertEquals(10, Carbon::parse($singleEvent->end)->day);
            }
        });
    }

    /** @test */
    public function create_everyMonthAtDayXEndOfMonth_correctAmountOfEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->createRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_MONTH_AT_DAY_X->value,
            recurrenceMetadata: 31
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertGreaterThan(28, Carbon::parse($singleEvent->start)->day);
                $this->assertGreaterThan(28, Carbon::parse($singleEvent->end)->day);
            }
        });
    }

    /** @test */
    public function create_everyLastDayInMonth_correctAmountOfEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->createRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_LAST_DAY_IN_MONTH->value,
            recurrenceMetadata: 0
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->get()->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertGreaterThan(21, Carbon::parse($singleEvent->start)->day);
                $this->assertGreaterThan(21, Carbon::parse($singleEvent->end)->day);
            }
        });
    }

    /** @test */
    public function create_everyFirstDayInMonth_correctAmountOfEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->createRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_FIRST_DAY_IN_MONTH->value,
            recurrenceMetadata: 3
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::WEDNESDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::WEDNESDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
        });
    }

    /** @test */
    public function create_everySecondDayInMonth_correctAmountOfEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->createRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_SECOND_DAY_IN_MONTH->value,
            recurrenceMetadata: 5
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::FRIDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::FRIDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
        });
    }

    /** @test */
    public function create_everyThirdDayInMonth_correctEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->createRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_THIRD_DAY_IN_MONTH->value,
            recurrenceMetadata: 6
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::SATURDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::SATURDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
        });
    }

    /** @test */
    public function update_notLoggedIn_returnsUnauthenticated()
    {
        // Act
        $response = $this->post("$this->basePath/anyGuid/update", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function update_notAuthorized_returnsUnauthorized()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->updateRequest(user: $user);

        // Assert
        $response->assertStatus(403);
    }


    /** @test */
    public function update_invalidDataMissingTitleDe_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingTitleDe: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.titleDe'));
    }

    /** @test */
    public function update_invalidDataMissingTitleEn_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingTitleEn: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.titleEn'));
    }

    /** @test */
    public function update_invalidDataMissingDescriptionDe_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingDescriptionDe: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.descriptionDe'));
    }

    /** @test */
    public function update_invalidDataMissingDescriptionEn_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingDescriptionEn: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.descriptionEn'));
    }

    /** @test */
    public function update_invalidDataMissingStart_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingStartFirstOccurrence: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.startFirstOccurrence'));
    }

    /** @test */
    public function update_invalidDataMissingEnd_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingEndFirstOccurrence: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.endFirstOccurrence'));
    }

    /** @test */
    public function update_invalidDataMissingRecurrence_returnsValidationError()
    {
        // Act
        $response = $this->createRequestWithMissing(missingRecurrence: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrence'));
    }

    /** @test */
    public function update_invalidDataMissingRecurrenceMetadata_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingRecurrenceMetadata: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrenceMetadata'));
    }

    /** @test */
    public function update_invalidDataMissingEventLocation_returnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingEventLocationGuid: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.eventLocationGuid'));
    }

    /** @test */
    public function update_invalidDataMissingFileUpload_ReturnsValidationError()
    {
        // Act
        $response = $this->updateRequestWithMissing(missingFileUploadGuid: true);

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.fileUploadGuid'));
    }

    /** @test */
    public function update_invalidDataInvalidRecurrence_returnsValidationError()
    {
        // Act
        $response = $this->updateRequest(recurrence: 'invalid');

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.recurrence'));
    }

    /** @test */
    public function update_invalidDataStartNotParsable_returnsValidationError()
    {
        // Act
        $response = $this->updateRequest(startFirstOccurrence: 'invalid');

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.startFirstOccurrence'));
    }

    /** @test */
    public function update_invalidDataEndFirstNotParsable_returnsValidationError()
    {
        // Act
        $response = $this->updateRequest(endFirstOccurrence: 'invalid');

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.endFirstOccurrence'));
    }

    /** @test */
    public function update_invalidDataEndNotParsable_returnsValidationError()
    {
        // Act
        $response = $this->updateRequest(endRecurrence: 'invalid');

        // Assert
        $response->assertStatus(422);
        $this->assertCount(1, $response->json('errors.endRecurrence'));
    }

    /** @test */
    public function update_invalidDataStartAfterEnd_returnsValidationError()
    {
        // Act
        $response = $this->updateRequest(startFirstOccurrence: '2024-01-06T16:30:00.0Z', endFirstOccurrence: '2024-01-06T14:00:00.0Z');

        // Assert
        $response->assertStatus(400);
        $response->assertContent('invalid time range');
    }

    /** @test */
    public function update_invalidDataEndBeforeFirstStart_returnsValidationError()
    {
        // Act
        $response = $this->updateRequest(
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            endRecurrence: '2024-01-05T18:00:00.0Z'
        );

        // Assert
        $response->assertStatus(400);
        $response->assertContent('invalid time range');
    }

    /** @test */
    public function update_validData_eventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();

        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(titleDe: $titleDe);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()
            ->where('title_de', $titleDe)
            ->get()
            ->first();

        $this->assertEquals($titleDe, $recurringEvent->title_de);
        $this->assertEquals($sample['titleEn'], $recurringEvent->title_en);
        $this->assertEquals($sample['descriptionDe'], $recurringEvent->description_de);
        $this->assertEquals($sample['descriptionEn'], $recurringEvent->description_en);
        $this->assertEquals($sample['recurrence'], $recurringEvent->recurrence->value);
        $this->assertEquals($sample['recurrenceMetadata'], $recurringEvent->recurrence_metadata);
        $this->assertEquals($this->eventLocation->guid, $recurringEvent->eventLocation()->get()->first()->guid);
        $this->assertEquals($this->fileUpload->guid, $recurringEvent->fileUpload()->get()->first()->guid);

        /** @var SingleEvent[] $singleEvents */
        $singleEvents = $recurringEvent->singleEvents()->get();

        foreach ($singleEvents as $singleEvent) {
            $this->assertEquals($titleDe, $singleEvent->title_de);
            $this->assertEquals($sample['titleEn'], $singleEvent->title_en);
            $this->assertEquals($sample['descriptionDe'], $singleEvent->description_de);
            $this->assertEquals($sample['descriptionEn'], $singleEvent->description_en);
            $this->assertTrue($singleEvent->is_recurring);
            $this->assertEquals($this->eventLocation->guid, $singleEvent->eventLocation()->get()->first()->guid);
            $this->assertEquals($this->fileUpload->guid, $singleEvent->fileUpload()->get()->first()->guid);
        }

    }

    /** @test */
    public function update_validData_recurringEventIsReturned()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();

        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(titleDe: $titleDe, startFirstOccurrence: '2024-01-06T16:30:00.0Z', endFirstOccurrence: '2024-01-06T18:00:00.0Z');

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());
        $response->assertJson(fn(AssertableJson $json) => $json->where('title_de', $titleDe)
            ->where('title_en', $sample['titleEn'])
            ->where('description_de', $sample['descriptionDe'])
            ->where('description_en', $sample['descriptionEn'])
            ->where("eventLocation.name", $this->eventLocation->name)
            ->where('eventLocation.street', $this->eventLocation->street)
            ->where('eventLocation.city', $this->eventLocation->city)
            ->where('image.fileUrl', 'http://localhost:8000/storage/' . $this->fileUpload->file_path)
            ->where('image.mimeType', 'image/png')
            ->where('recurrence', $sample['recurrence'])
            ->where('recurrenceMetadata', $sample['recurrenceMetadata'])
            ->etc());
    }

    /** @test */
    public function update_everyMonthAtDayX_correctEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();

        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_MONTH_AT_DAY_X->value,
            recurrenceMetadata: 10
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->get()->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(10, Carbon::parse($singleEvent->start)->day);
                $this->assertEquals(10, Carbon::parse($singleEvent->end)->day);
            }
        });
    }

    /** @test */
    public function update_everyMonthAtDayXEndOfMonth_correctAmountOfEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_MONTH_AT_DAY_X->value,
            recurrenceMetadata: 31
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertGreaterThan(28, Carbon::parse($singleEvent->start)->day);
                $this->assertGreaterThan(28, Carbon::parse($singleEvent->end)->day);
            }
        });
    }

    /** @test */
    public function update_everyLastDayInMonth_correctAmountOfEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_LAST_DAY_IN_MONTH->value,
            recurrenceMetadata: 0
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->get()->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertGreaterThan(21, Carbon::parse($singleEvent->start)->day);
                $this->assertGreaterThan(21, Carbon::parse($singleEvent->end)->day);
            }
        });
    }

    /** @test */
    public function update_everyFirstDayInMonth_correctAmountOfEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_FIRST_DAY_IN_MONTH->value,
            recurrenceMetadata: 3
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::WEDNESDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::WEDNESDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
        });
    }

    /** @test */
    public function update_everySecondDayInMonth_correctAmountOfEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_SECOND_DAY_IN_MONTH->value,
            recurrenceMetadata: 5
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::FRIDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::FRIDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
        });
    }

    /** @test */
    public function update_everyThirdDayInMonth_correctEventsAreCreated()
    {
        // Arrange
        $singleEventCount = SingleEvent::query()->count();
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_THIRD_DAY_IN_MONTH->value,
            recurrenceMetadata: 6
        );

        // Assert
        $response->assertStatus(200);
        $this->assertEquals($singleEventCount + 12, SingleEvent::query()->count());

        /** @var RecurringEvent $recurringEvent */
        $recurringEvent = RecurringEvent::query()->where('title_de', $titleDe)->first();

        $this->assertSingleEvents($recurringEvent, function ($singleEvent, $recurringEvent) {
            if (Carbon::parse($singleEvent->start)->day !== Carbon::parse($recurringEvent->start_first_occurrence)->day) {
                $this->assertEquals(CarbonInterface::SATURDAY, Carbon::parse($singleEvent->start)->dayOfWeek);
                $this->assertEquals(CarbonInterface::SATURDAY, Carbon::parse($singleEvent->end)->dayOfWeek);
            }
        });
    }

    /** @test */
    public function update_allValid_oldSingleEventsAreDeleted()
    {
        // Arrange
        $sample = static::getTestEventData();
        $titleDe = $sample['titleDe'] . uuid_create();

        $oldEvent = RecurringEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($this->user, 'uploadedBy')->create())
            ->for($this->user, 'createdBy')
            ->create();

        $singleEvent = SingleEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for($this->user, 'uploadedBy')->create())
            ->for($this->user, 'createdBy')
            ->create();

        $singleEvent->recurringEvent()->associate($oldEvent);
        $singleEvent->save();

        // Act
        $this->updateRequest(
            oldEventGuid: $oldEvent->guid,
            titleDe: $titleDe,
            startFirstOccurrence: '2024-01-06T16:30:00.0Z',
            endFirstOccurrence: '2024-01-06T18:00:00.0Z',
            recurrence: Recurrence::EVERY_THIRD_DAY_IN_MONTH->value,
            recurrenceMetadata: 6
        );

        // Assert
        $this->assertCount(0, SingleEvent::query()->where('guid', $singleEvent->guid)->get());
    }

    private static function getTestEventData(): array
    {
        return [
            'titleDe' => 'test title de',
            'titleEn' => 'test title en',
            'descriptionDe' => 'test description de',
            'descriptionEn' => 'test description en',
            'startFirstOccurrence' => '2024-01-06T16:30:00.0Z',
            'endFirstOccurrence' => '2024-01-06T18:00:00.0Z',
            'recurrence' => Recurrence::EVERY_X_DAYS->value,
            'recurrenceMetadata' => 31,
            'endRecurrence' => '2025-01-06T16:30:00.0Z'
        ];
    }

    private function createRequest(
        ?User                  $user = null,
        ?string                $titleDe = null,
        ?string                $titleEn = null,
        ?string                $descriptionDe = null,
        ?string                $descriptionEn = null,
        ?string                $startFirstOccurrence = null,
        ?string                $endFirstOccurrence = null,
        Recurrence|string|null $recurrence = null,
        ?int                   $recurrenceMetadata = null,
        ?string                $eventLocationGuid = null,
        ?string                $fileUploadGuid = null,
        ?string                $endRecurrence = null,
    ): TestResponse
    {
        $sample = static::getTestEventData();
        return $this->actingAs($user ?? $this->user)->put("$this->basePath", [
            'titleEn' => $titleEn ?? $sample['titleEn'],
            'titleDe' => $titleDe ?? $sample['titleDe'],
            'descriptionDe' => $descriptionDe ?? $sample['descriptionDe'],
            'descriptionEn' => $descriptionEn ?? $sample['descriptionEn'],
            'eventLocationGuid' => $eventLocationGuid ?? $this->eventLocation->guid,
            'fileUploadGuid' => $fileUploadGuid ?? $this->fileUpload->guid,
            'startFirstOccurrence' => $startFirstOccurrence ?? $sample['startFirstOccurrence'],
            'endFirstOccurrence' => $endFirstOccurrence ?? $sample['endFirstOccurrence'],
            'recurrence' => $recurrence ?? $sample['recurrence'],
            'recurrenceMetadata' => $recurrenceMetadata ?? $sample['recurrenceMetadata'],
            'endRecurrence' => $endRecurrence ?? $sample['endRecurrence']
        ], ['Accept' => 'application/json']);
    }

    private function createRequestWithMissing(
        bool $missingTitleDe = null,
        bool $missingTitleEn = null,
        bool $missingDescriptionDe = null,
        bool $missingDescriptionEn = null,
        bool $missingStartFirstOccurrence = null,
        bool $missingEndFirstOccurrence = null,
        bool $missingRecurrence = null,
        bool $missingRecurrenceMetadata = null,
        bool $missingEventLocationGuid = null,
        bool $missingFileUploadGuid = null
    ): TestResponse
    {
        $sample = static::getTestEventData();
        return $this->actingAs($user ?? $this->user)->put("$this->basePath", [
            'titleEn' => $missingTitleEn ? null : $sample['titleEn'],
            'titleDe' => $missingTitleDe ? null : $sample['titleDe'],
            'descriptionDe' => $missingDescriptionDe ? null : $sample['descriptionDe'],
            'descriptionEn' => $missingDescriptionEn ? null : $sample['descriptionEn'],
            'eventLocationGuid' => $missingEventLocationGuid ? null : $this->eventLocation->guid,
            'fileUploadGuid' => $missingFileUploadGuid ? null : $this->fileUpload->guid,
            'startFirstOccurrence' => $missingStartFirstOccurrence ? null : $sample['startFirstOccurrence'],
            'endFirstOccurrence' => $missingEndFirstOccurrence ? null : $sample['endFirstOccurrence'],
            'recurrence' => $missingRecurrence ? null : $sample['recurrence'],
            'recurrenceMetadata' => $missingRecurrenceMetadata ? null : $sample['recurrenceMetadata']
        ], ['Accept' => 'application/json']);
    }

    private function updateRequest(
        ?User                  $user = null,
        ?string                $oldEventGuid = null,
        ?string                $titleDe = null,
        ?string                $titleEn = null,
        ?string                $descriptionDe = null,
        ?string                $descriptionEn = null,
        ?string                $startFirstOccurrence = null,
        ?string                $endFirstOccurrence = null,
        Recurrence|string|null $recurrence = null,
        ?int                   $recurrenceMetadata = null,
        ?string                $eventLocationGuid = null,
        ?string                $fileUploadGuid = null,
        ?string                $endRecurrence = null,
    ): TestResponse
    {
        $sample = static::getTestEventData();
        if (is_null($oldEventGuid)) {
            $oldEVent = RecurringEvent::factory()
                ->for(EventLocation::factory()->create())
                ->for(FileUpload::factory()->for($user ?? $this->user, 'uploadedBy')->create())
                ->for($user ?? $this->user, 'createdBy')
                ->create();
            $oldEventGuid = $oldEVent->guid;
        }
        return $this->actingAs($user ?? $this->user)->post("$this->basePath/$oldEventGuid/update", [
            'titleEn' => $titleEn ?? $sample['titleEn'],
            'titleDe' => $titleDe ?? $sample['titleDe'],
            'descriptionDe' => $descriptionDe ?? $sample['descriptionDe'],
            'descriptionEn' => $descriptionEn ?? $sample['descriptionEn'],
            'eventLocationGuid' => $eventLocationGuid ?? $this->eventLocation->guid,
            'fileUploadGuid' => $fileUploadGuid ?? $this->fileUpload->guid,
            'startFirstOccurrence' => $startFirstOccurrence ?? $sample['startFirstOccurrence'],
            'endFirstOccurrence' => $endFirstOccurrence ?? $sample['endFirstOccurrence'],
            'recurrence' => $recurrence ?? $sample['recurrence'],
            'recurrenceMetadata' => $recurrenceMetadata ?? $sample['recurrenceMetadata'],
            'endRecurrence' => $endRecurrence ?? $sample['endRecurrence']
        ], ['Accept' => 'application/json']);
    }

    private function updateRequestWithMissing(
        ?string $oldEventGuid = null,
        bool    $missingTitleDe = null,
        bool    $missingTitleEn = null,
        bool    $missingDescriptionDe = null,
        bool    $missingDescriptionEn = null,
        bool    $missingStartFirstOccurrence = null,
        bool    $missingEndFirstOccurrence = null,
        bool    $missingRecurrence = null,
        bool    $missingRecurrenceMetadata = null,
        bool    $missingEventLocationGuid = null,
        bool    $missingFileUploadGuid = null
    ): TestResponse
    {
        $sample = static::getTestEventData();
        if (is_null($oldEventGuid)) {
            $oldEVent = RecurringEvent::factory()
                ->for(EventLocation::factory()->create())
                ->for(FileUpload::factory()->for($user ?? $this->user, 'uploadedBy')->create())
                ->for($user ?? $this->user, 'createdBy')
                ->create();
            $oldEventGuid = $oldEVent->guid;
        }
        return $this->actingAs($user ?? $this->user)->post("$this->basePath/$oldEventGuid/update", [
            'titleEn' => $missingTitleEn ? null : $sample['titleEn'],
            'titleDe' => $missingTitleDe ? null : $sample['titleDe'],
            'descriptionDe' => $missingDescriptionDe ? null : $sample['descriptionDe'],
            'descriptionEn' => $missingDescriptionEn ? null : $sample['descriptionEn'],
            'eventLocationGuid' => $missingEventLocationGuid ? null : $this->eventLocation->guid,
            'fileUploadGuid' => $missingFileUploadGuid ? null : $this->fileUpload->guid,
            'startFirstOccurrence' => $missingStartFirstOccurrence ? null : $sample['startFirstOccurrence'],
            'endFirstOccurrence' => $missingEndFirstOccurrence ? null : $sample['endFirstOccurrence'],
            'recurrence' => $missingRecurrence ? null : $sample['recurrence'],
            'recurrenceMetadata' => $missingRecurrenceMetadata ? null : $sample['recurrenceMetadata']
        ], ['Accept' => 'application/json']);
    }

    private function assertSingleEvents(RecurringEvent $recurringEvent, $assertDays): void
    {
        /** @var SingleEvent[] $singleEVents */
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

            $assertDays($singleEvent, $recurringEvent);
            $months[] = Carbon::parse($singleEvent->start)->month;
        }
        $this->assertSameSize($months, array_unique($months));
    }
}
