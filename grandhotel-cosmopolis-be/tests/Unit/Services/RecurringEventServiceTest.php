<?php

namespace Tests\Unit\Services;

use App\Exceptions\InvalidTimeRangeException;
use App\Http\Controllers\Event\Recurrence;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\User;
use App\Repositories\Interfaces\IRecurringEventRepository;
use App\Repositories\Interfaces\ISingleEventRepository;
use App\Services\Interfaces\RecurringEventService;
use App\Services\TimeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class RecurringEventServiceTest extends TestCase
{

    use RefreshDatabase;

    private RecurringEventService $cut;

    private IRecurringEventRepository & MockObject $recurringEventRepositoryMock;
    private ISingleEventRepository & MockObject $singleEventRepositoryMock;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->recurringEventRepositoryMock = $this->getMockBuilder(IRecurringEventRepository::class)->getMock();
        $this->singleEventRepositoryMock = $this->getMockBuilder(ISingleEventRepository::class)->getMock();
        $this->cut = new RecurringEventService(new TimeService(), $this->recurringEventRepositoryMock, $this->singleEventRepositoryMock);
    }

    /** @test */
    public function create_invalidTimeRange_throwsException()
    {
        // Arrange
        $this->expectException(InvalidTimeRangeException::class);

        // Act & Assert
        $this->cut->create(
            'test',
            'test',
            'test',
            'test',
            Carbon::now(),
            Carbon::now()->subHour(),
            Carbon::now()->addYear(),
            Recurrence::EVERY_X_DAYS,
            1,
            'eventLocationGuid',
            'fileUploadGuid'
        );
    }

    /** @test */
    public function create_invalidEndRecurrence_throwsException()
    {
        // Arrange
        $this->expectException(InvalidTimeRangeException::class);

        // Act & Assert
        $this->cut->create(
            'test',
            'test',
            'test',
            'test',
            Carbon::now(),
            Carbon::now()->addHour(),
            Carbon::now()->subYear(),
            Recurrence::EVERY_X_DAYS,
            1,
            'eventLocationGuid',
            'fileUploadGuid'
        );
    }

    /** @test */
    public function create_allValid_repositoriesAreCorrectlyCalled()
    {
        // Arrange
        $recurringEvent = RecurringEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for(User::factory()->create(), 'uploadedBy')->create())
            ->for(User::factory()->create(), 'createdBy')
            ->make([
                'start_first_occurrence' => Carbon::parse('2024-01-11T18:00:00.000Z'),
                'end_first_occurrence' => Carbon::parse('2024-01-11T20:00:00.000Z'),
                'recurrence' => Recurrence::EVERY_X_DAYS,
                'recurrence_metadata' => 1,
                'end_recurrence' => Carbon::parse('2024-02-11T18:00:00.000Z')
            ]);
        $this->recurringEventRepositoryMock->expects($this->once())
            ->method('create')
            ->willReturn($recurringEvent);
        $this->singleEventRepositoryMock->expects($this->exactly(31))
            ->method('createSingleEvent');

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->create(
            'test',
            'test',
            'test',
            'test',
            Carbon::parse('2024-01-11T18:00:00.000Z'),
            Carbon::parse('2024-01-11T20:00:00.000Z'),
            Carbon::parse('2024-02-11T18:00:00.000Z'),
            Recurrence::EVERY_X_DAYS,
            1,
            'eventLocationGuid',
            'fileUploadGuid'
        );
    }

    /** @test */
    public function create_allValid_repositoriesAreCorrectlyCalledForEndOfYear()
    {
        // Arrange
        $recurringEvent = RecurringEvent::factory()
            ->for(EventLocation::factory()->create())
            ->for(FileUpload::factory()->for(User::factory()->create(), 'uploadedBy')->create())
            ->for(User::factory()->create(), 'createdBy')
            ->make([
                'start_first_occurrence' => Carbon::parse('2024-11-01T18:00:00.000Z'),
                'end_first_occurrence' => Carbon::parse('2024-11-01T20:00:00.000Z'),
                'recurrence' => Recurrence::EVERY_X_DAYS,
                'recurrence_metadata' => 1,
                'end_recurrence' => Carbon::parse('2025-02-11T18:00:00.000Z')
            ]);
        $this->recurringEventRepositoryMock->expects($this->once())
            ->method('create')
            ->willReturn($recurringEvent);
        $this->singleEventRepositoryMock->expects($this->exactly(61))
            ->method('createSingleEvent');

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->create(
            'test',
            'test',
            'test',
            'test',
            Carbon::parse('2024-11-01T18:00:00.000Z'),
            Carbon::parse('2024-11-01T20:00:00.000Z'),
            Carbon::parse('2025-02-11T18:00:00.000Z'),
            Recurrence::EVERY_X_DAYS,
            1,
            'eventLocationGuid',
            'fileUploadGuid'
        );
    }
}
