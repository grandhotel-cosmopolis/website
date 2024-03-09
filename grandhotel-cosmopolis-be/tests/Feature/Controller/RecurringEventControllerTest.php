<?php

namespace Tests\Feature\Controller;

use App\Http\Controllers\Event\Recurrence;
use App\Http\Dtos\Event\RecurringEventDto;
use App\Http\Dtos\Event\SingleEventDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\Permissions;
use App\Models\RecurringEvent;
use App\Models\SingleEvent;
use App\Models\SingleEventException;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
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
            Permissions::UNPUBLISH_EVENT,
            Permissions::VIEW_EVENTS
        ]);
        $this->fileUpload = FileUpload::factory()->for($this->user, 'uploadedBy')->create();
        $this->eventLocation = EventLocation::factory()->create();
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
        $response->assertJson(fn(AssertableJson $json) => $json->where('titleDe', $titleDe)
            ->where('titleEn', $sample['titleEn'])
            ->where('descriptionDe', $sample['descriptionDe'])
            ->where('descriptionEn', $sample['descriptionEn'])
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
    public function update_oldEventHadSingleEventWithException_exceptionIsDeleted()
    {
        // Arrange
        /** @var SingleEvent $event */
        $title = uuid_create();
        $event = SingleEVent::factory()
            ->for(User::factory()->create(), 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for(User::factory()->create(), 'uploadedBy'))
            ->create();

        $exception = new SingleEventException([
            'title_de' => $title,
            'title_en' => 'title en exception',
            'end' => $event->end->clone()->addHour()
        ]);
        $exception->singleEvent()->associate($event);
        $exception->save();
        /** @var RecurringEvent $oldRecurringEvent */
        $oldRecurringEvent = $this::createRecurringEvent()->create();
        $event->recurringEvent()->associate($oldRecurringEvent);
        $event->save();

        $sample = static::getTestEventData();

        $titleDe = $sample['titleDe'] . uuid_create();

        // Act
        $response = $this->updateRequest(oldEventGuid: $oldRecurringEvent->guid, titleDe: $titleDe);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(0, SingleEventException::query()->where('title_de', $title)->get());

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
        $response->assertJson(fn(AssertableJson $json) => $json->where('titleDe', $titleDe)
            ->where('titleEn', $sample['titleEn'])
            ->where('descriptionDe', $sample['descriptionDe'])
            ->where('descriptionEn', $sample['descriptionEn'])
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

    /** @test */
    public function delete_notAuthenticated_returnsUnauthenticated()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createRecurringEvent()->create();

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
        $oldEvent = static::createRecurringEvent()->create();
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
        /** @var RecurringEvent $oldEvent */
        $oldEvent = static::createRecurringEvent()->create();

        // Act
        $response = $this->deleteRequest($oldEvent->guid);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals(0, RecurringEvent::query()->where('guid', $oldEvent->guid)->count());
    }

    /** @test */
    public function delete_allValid_singleEventsAreIsDeleted()
    {
        // Arrange
        /** @var RecurringEvent $oldEvent */
        $oldEvent = static::createRecurringEvent()->create();
        $singleEvents = SingleEvent::factory()
            ->for($this->eventLocation)
            ->for($this->fileUpload)
            ->for($this->user, 'createdBy')
            ->count(10)
            ->create();

        /** @var string[] $singleEventGuids */
        $singleEventGuids = [];

        /** @var SingleEvent $singleEvent */
        foreach ($singleEvents as $singleEvent) {
            $singleEvent->recurringEvent()->associate($oldEvent);
            $singleEvent->save();
            $singleEventGuids[] = $singleEvent->guid;
        }

        // Act
        $response = $this->deleteRequest($oldEvent->guid);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals(0, RecurringEvent::query()->where('guid', $oldEvent->guid)->count());

        foreach ($singleEventGuids as $singleEventGuid) {
            $this->assertCount(0, SingleEvent::query()->where('guid', $singleEventGuid)->get());
        }
    }

    public function deleteRequest(string|null $oldEventGuid = null): TestResponse
    {
        if (is_null($oldEventGuid)) {
            /** @var SingleEvent $oldEvent */
            $oldEvent = static::createRecurringEvent()->create();
            $oldEventGuid = $oldEvent->guid;
        }


        return $this->actingAs($this->user)->delete("$this->basePath/$oldEventGuid", [], ['Accept' => 'application/json']);
    }

    /** @test */
    public function publish_notAuthenticated_returnsUnauthenticated()
    {
        // Arrange
        /** @var RecurringEvent $oldEvent */
        $oldEvent = static::createRecurringEvent()->create();

        // Act
        $response = $this->post("$this->basePath/$oldEvent->guid/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function publish_notAuthorized_returnsUnauthorized()
    {
        // Arrange
        /** @var RecurringEvent $oldEvent */
        $oldEvent = static::createRecurringEvent()->create();
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
        /** @var RecurringEvent $oldEvent */
        $oldEvent = static::createRecurringEvent()->create(['is_public' => false]);
        $singleEvents = SingleEvent::factory()
            ->for($this->eventLocation)
            ->for($this->fileUpload)
            ->for($this->user, 'createdBy')
            ->count(10)
            ->create(['is_public' => false]);

        /** @var string[] $singleEventGuids */
        $singleEventGuids = [];

        /** @var SingleEvent $singleEvent */
        foreach ($singleEvents as $singleEvent) {
            $singleEvent->recurringEvent()->associate($oldEvent);
            $singleEvent->save();
            $singleEventGuids[] = $singleEvent->guid;
        }

        // Act
        $response = $this->actingAs($this->user)->post("$this->basePath/$oldEvent->guid/publish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var RecurringEvent $updatedEvent */
        $updatedEvent = RecurringEvent::query()->where('guid', $oldEvent->guid)->first();
        $this->assertTrue($updatedEvent->is_public);

        foreach ($singleEventGuids as $singleEventGuid) {
            /** @var SingleEvent $singleEvent */
            $singleEvent = SingleEvent::query()->where('guid', $singleEventGuid)->first();
            $this->assertTrue($singleEvent->is_public);
        }
    }

    /** @test */
    public function unpublish_notAuthenticated_returnsUnauthenticated()
    {
        // Arrange
        /** @var SingleEvent $oldEvent */
        $oldEvent = static::createRecurringEvent()->create();

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
        $oldEvent = static::createRecurringEvent()->create();
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
        /** @var RecurringEvent $oldEvent */
        $oldEvent = static::createRecurringEvent()->create(['is_public' => true]);
        $singleEvents = SingleEvent::factory()
            ->for($this->eventLocation)
            ->for($this->fileUpload)
            ->for($this->user, 'createdBy')
            ->count(10)
            ->create(['is_public' => true]);

        /** @var string[] $singleEventGuids */
        $singleEventGuids = [];

        /** @var SingleEvent $singleEvent */
        foreach ($singleEvents as $singleEvent) {
            $singleEvent->recurringEvent()->associate($oldEvent);
            $singleEvent->save();
            $singleEventGuids[] = $singleEvent->guid;
        }

        // Act
        $response = $this->actingAs($this->user)->post("$this->basePath/$oldEvent->guid/unpublish", [], ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var RecurringEvent $updatedEvent */
        $updatedEvent = RecurringEvent::query()->where('guid', $oldEvent->guid)->first();
        $this->assertFalse($updatedEvent->is_public);

        foreach ($singleEventGuids as $singleEventGuid) {
            /** @var SingleEvent $singleEvent */
            $singleEvent = SingleEvent::query()->where('guid', $singleEventGuid)->first();
            $this->assertFalse($singleEvent->is_public);
        }
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
        self::createRecurringEvent()
            ->count(10)
            ->create(['is_public' => true]);
        self::createRecurringEvent()
            ->count(5)
            ->create(['is_public' => false]);

        // Act
        $response = $this->actingAs($this->user)->get("$this->basePath/listAll", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var RecurringEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(15, $events);
    }

    /** @test */
    public function listAll_allValid_doesNotReturnEventsInThePast()
    {
        // Arrange
        self::createRecurringEvent()
            ->count(10)
            ->create([
                'end_recurrence' => new Carbon('2024-01-11T18:00:00.000Z')
            ]);
        self::createRecurringEvent()
            ->count(5)
            ->create(['is_public' => false]);

        // Act
        $response = $this->actingAs($this->user)->get("$this->basePath/listAll", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var RecurringEvent[] $events */
        $events = $response->json('events');
        $this->assertCount(5, $events);
    }

    /** @test */
    public function listAllSingleEventsByRecurringEventId_notLoggedIn_returnsUnauthorized()
    {
        // Act
        $response = $this->get("$this->basePath/eventId/listSingleEvents", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function listAllSingleEventsByRecurringEventId_unauthorized_returnsUnauthorized()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get("$this->basePath/eventId/listSingleEvents", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(403);
    }

    /** @test */
    public function listAllSingleEventsByRecurringEventId_allValid_returnsPublicAndPrivateEvents()
    {
        // Arrange
        /** @var RecurringEvent $event */
        $event = self::createRecurringEvent()
            ->create(['is_public' => true]);
        SingleEvent::factory()
            ->for($this->eventLocation)
            ->for($this->fileUpload)
            ->for($this->user, 'createdBy')
            ->for($event, 'recurringEvent')
            ->count(10)
            ->create(['is_public' => true]);

        SingleEvent::factory()
            ->for($this->eventLocation)
            ->for($this->fileUpload)
            ->for($this->user, 'createdBy')
            ->for($event, 'recurringEvent')
            ->count(5)
            ->create(['is_public' => false]);

        // Act
        $response = $this->actingAs($this->user)->get("$this->basePath/$event->guid/listSingleEvents", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(15, $events);
    }

    /** @test */
    public function listAllSingleEventsByRecurringEventId_allValid_doesNotReturnEventsInThePast()
    {
        // Arrange
        /** @var RecurringEvent $event */
        $event = self::createRecurringEvent()
            ->create();
        SingleEvent::factory()
            ->for($this->eventLocation)
            ->for($this->fileUpload)
            ->for($this->user, 'createdBy')
            ->for($event, 'recurringEvent')
            ->count(10)
            ->create([
                'end' => new Carbon('2024-01-11T18:00:00.000Z')
            ]);

        SingleEvent::factory()
            ->for($this->eventLocation)
            ->for($this->fileUpload)
            ->for($this->user, 'createdBy')
            ->for($event, 'recurringEvent')
            ->count(5)
            ->create();

        // Act
        $response = $this->actingAs($this->user)->get("$this->basePath/$event->guid/listSingleEvents", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(200);
        /** @var SingleEventDto[] $events */
        $events = $response->json('events');
        $this->assertCount(5, $events);
    }

    /** @test */
    public function listSingleEvents_unknownEvent_returnsNotFound()
    {
        // Act
        $response = $this->actingAs($this->user)->get("$this->basePath/unknown/listSingleEvents", ['Accept' => 'application/json']);

        // Assert
        $response->assertStatus(404);
    }

    private static function createRecurringEvent(
        ?EventLocation $eventLocation = null,
        ?User          $user = null,
        ?FileUpload    $fileUpload = null
    ): Factory
    {
        $eventLocation = !is_null($eventLocation) ? $eventLocation : EventLocation::factory()->create();
        $user = !is_null($user) ? $user : User::factory()->create();
        $fileUpload = !is_null($fileUpload) ? $fileUpload : FileUpload::factory()->for($user, 'uploadedBy')->create();
        return RecurringEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy');
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
        ?bool                  $isPublic = null,
    ): TestResponse
    {
        $sample = static::getTestEventData();
        return $this->actingAs($user ?? $this->user)->post("$this->basePath", [
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
            'endRecurrence' => $endRecurrence ?? $sample['endRecurrence'],
            'isPublic' => $isPublic ?? false
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
        return $this->actingAs($user ?? $this->user)->post("$this->basePath", [
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
            $this->assertCount(0, $singleEvent->exception()->get());
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
