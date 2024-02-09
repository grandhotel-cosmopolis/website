<?php

namespace Tests\Unit\Services;

use App\Exceptions\InvalidTimeRangeException;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\User;
use App\Repositories\Interfaces\ISingleEventRepository;
use App\Services\SingleEventService;
use App\Services\TimeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class SingleEventServiceTest extends TestCase
{
    use RefreshDatabase;

    private SingleEventService $cut;

    private ISingleEventRepository & MockObject $singleEventRepositoryMock;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->singleEventRepositoryMock = $this->getMockBuilder(ISingleEventRepository::class)->getMock();
        $this->cut = new SingleEventService($this->singleEventRepositoryMock, new TimeService());
    }

    /** @test */
    public function createSingleEvent_invalidFileUpload_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $evenLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for($evenLocation)
            ->for($fileUpload)
            ->make();

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            $evenLocation->guid,
            'invalid'
        );
    }

    /** @test */
    public function createSingleEvent_invalidEventLocation_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $evenLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for($evenLocation)
            ->for($fileUpload)
            ->make();

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            'invalid',
            $fileUpload->guid
        );
    }

    /** @test */
    public function createSingleEvent_invalidTimeRange_ThrowsException() {
        // Arrange
        $this->expectException(InvalidTimeRangeException::class);
        $user = User::factory()->create();
        $evenLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for($evenLocation)
            ->for($fileUpload)
            ->make();

        // Act & Assert
        $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->end,
            $singleEvent->end->subMinutes(30),
            $evenLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function createSingleEvent_allValid_repositoryIsCalled() {
        // Arrange
        $user = User::factory()->create();
        $evenLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for($evenLocation)
            ->for($fileUpload)
            ->make();
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('createSingleEvent');

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createSingleEvent(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            $evenLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function updateSingleEvent_invalidFileUpload_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $evenLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $existingEvent = SingleEvent::factory()
                ->for($user, 'createdBy')
                ->for(EventLocation::factory()->create())
                ->for($fileUpload)
                ->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for($evenLocation)
            ->for($fileUpload)
            ->make();

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->updateSingleEvent(
            $existingEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            $evenLocation->guid,
            'invalid'
        );
    }

    /** @test */
    public function updateSingleEvent_invalidEventLocation_ThrowsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);
        $user = User::factory()->create();
        $evenLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $existingEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for($fileUpload)
            ->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for($evenLocation)
            ->for($fileUpload)
            ->make();

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->updateSingleEvent(
            $existingEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            $evenLocation->guid,
            'invalid'
        );
    }

    /** @test */
    public function updateSingleEvent_invalidTimeRange_ThrowsException() {
        // Arrange
        $this->expectException(InvalidTimeRangeException::class);
        $user = User::factory()->create();
        $evenLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $existingEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for($fileUpload)
            ->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for($evenLocation)
            ->for($fileUpload)
            ->make();

        // Act & Assert
        $this->cut->updateSingleEvent(
            $existingEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->end,
            $singleEvent->end->subMinutes(30),
            $evenLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function updateSingleEvent_allValid_repositoryIsCalled() {
        // Arrange
        $user = User::factory()->create();
        $evenLocation = EventLocation::factory()->create();
        $fileUpload = FileUpload::factory()->for($user, 'uploadedBy')->create();
        $existingEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for(EventLocation::factory()->create())
            ->for($fileUpload)
            ->create();
        $singleEvent = SingleEvent::factory()
            ->for($user, 'createdBy')
            ->for($evenLocation)
            ->for($fileUpload)
            ->make(['guid' => $existingEvent->guid]);
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('updateSingleEvent')
            ->willReturn($singleEvent);

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->updateSingleEvent(
            $existingEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            $evenLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function deleteSingleEvent_allValid_repositoryIsCalled() {
        // Arrange
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('deleteSingleEvent')
            ->with('test-guid');

        // Act & Assert
        $this->cut->deleteSingleEvent('test-guid');
    }

    /** @test */
    public function publishSingleEvent_allValid_repositoryIsCalled() {
        // Arrange
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('publishSingleEvent')
            ->with('test-guid');

        // Act & Assert
        $this->cut->publishSingleEvent('test-guid');
    }

    /** @test */
    public function unpublishSingleEvent_allValid_repositoryIsCalled() {
        // Arrange
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('unpublishSingleEvent')
            ->with('test-guid');

        // Act & Assert
        $this->cut->unpublishSingleEvent('test-guid');
    }

    /** @test */
    public function getSingleEvents_allValid_repositoryIsCalled() {
        // Arrange
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('getSingleEvents');

        // Act & Assert
        $this->cut->getSingleEvents(Carbon::now(), Carbon::now()->addWeeks(3));
    }
}
