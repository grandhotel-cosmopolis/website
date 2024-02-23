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
    public function create_invalidFileUpload_throwsException() {
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
        $this->cut->create(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $evenLocation->guid,
            'invalid'
        );
    }

    /** @test */
    public function create_invalidEventLocation_throwsException() {
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
        $this->cut->create(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            'invalid',
            $fileUpload->guid
        );
    }

    /** @test */
    public function create_invalidTimeRange_throwsException() {
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
        $this->cut->create(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->end,
            $singleEvent->end->subMinutes(30),
            false,
            $evenLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function create_allValid_repositoryIsCalled() {
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
        $this->cut->create(
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $evenLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function update_invalidFileUpload_throwsException() {
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
        $this->cut->update(
            $existingEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $evenLocation->guid,
            'invalid'
        );
    }

    /** @test */
    public function update_invalidEventLocation_throwsException() {
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
        $this->cut->update(
            $existingEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $evenLocation->guid,
            'invalid'
        );
    }

    /** @test */
    public function update_invalidTimeRange_throwsException() {
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
        $this->cut->update(
            $existingEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->end,
            $singleEvent->end->subMinutes(30),
            false,
            $evenLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function update_allValid_repositoryIsCalled() {
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
        $this->cut->update(
            $existingEvent->guid,
            $singleEvent->title_de,
            $singleEvent->title_en,
            $singleEvent->description_de,
            $singleEvent->description_en,
            $singleEvent->start,
            $singleEvent->end,
            false,
            $evenLocation->guid,
            $fileUpload->guid
        );
    }

    /** @test */
    public function delete_allValid_repositoryIsCalled() {
        // Arrange
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('deleteSingleEvent')
            ->with('test-guid');

        // Act & Assert
        $this->cut->delete('test-guid');
    }

    /** @test */
    public function publish_allValid_repositoryIsCalled() {
        // Arrange
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('publishSingleEvent')
            ->with('test-guid');

        // Act & Assert
        $this->cut->publish('test-guid');
    }

    /** @test */
    public function unpublish_allValid_repositoryIsCalled() {
        // Arrange
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('unpublishSingleEvent')
            ->with('test-guid');

        // Act & Assert
        $this->cut->unpublish('test-guid');
    }

    /** @test */
    public function list_allValid_repositoryIsCalled() {
        // Arrange
        $this->singleEventRepositoryMock->expects($this->once())
            ->method('getSingleEvents');

        // Act & Assert
        $this->cut->list(Carbon::now(), Carbon::now()->addWeeks(3));
    }
}
