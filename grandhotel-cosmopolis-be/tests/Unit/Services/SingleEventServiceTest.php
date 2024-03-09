<?php

namespace Tests\Unit\Services;

use App\Exceptions\InvalidTimeRangeException;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\SingleEventException;
use App\Models\User;
use App\Repositories\Interfaces\ISingleEventRepository;
use App\Services\SingleEventService;
use App\Services\TimeService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
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

    /** @test */
    public function createOrUpdateEventException_unknownSingleEvent_throwsException() {
        // Arrange
        $this->expectException(NotFoundHttpException::class);

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException('unknown', null, null, null, null, null, null, null, null);
    }

    /** @test */
    public function createOrUpdateEventException_invalidTimeRangeStartAndEndSet_throwsException() {
        // Arrange
        $this->expectException(InvalidTimeRangeException::class);
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        $start = Carbon::now();
        $end = Carbon::now()->subHour();

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            null,
            null,
            $start,
            $end,
            null,
            null
        );
    }

    /** @test */
    public function createOrUpdateEventException_invalidTimeRangeStartIsSet_throwsException() {
        // Arrange
        $this->expectException(InvalidTimeRangeException::class);
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        $start = $singleEvent->end->clone()->addHour();

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            null,
            null,
            $start,
            null,
            null,
            null
        );
    }

    /** @test */
    public function createOrUpdateEventException_invalidTimeRangeEndIsSet_throwsException() {
        // Arrange
        $this->expectException(InvalidTimeRangeException::class);
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        $end = $singleEvent->start->subHour();

        // Act & Assert
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            null,
            null,
            null,
            $end,
            null,
            null
        );
    }

    /** @test */
    public function createOrUpdateEventException_onlyTitle_exceptionIsStored() {
        // Arrange
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            'exception title de',
            'exception title en',
            null,
            null,
            null,
            null,
            null,
            null
        );

        // Assert
        /** @var SingleEventException $singleEventException */
        $singleEventException = $singleEvent->exception()->first();

        $this->assertNotNull($singleEventException);
        $this->assertEquals('exception title de', $singleEventException->title_de);
        $this->assertEquals('exception title en', $singleEventException->title_en);
    }

    /** @test */
    public function createOrUpdateEventException_onlyDescription_exceptionIsStored() {
        // Arrange
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        $descriptionDe = 'exception description de';
        $descriptionEn = 'exception description en';

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            $descriptionDe,
            $descriptionEn,
            null,
            null,
            null,
            null
        );

        // Assert
        $singleEvent->refresh();

        /** @var SingleEventException $singleEventException */
        $singleEventException = $singleEvent->exception()->first();

        $this->assertNotNull($singleEventException);
        $this->assertEquals($descriptionDe, $singleEventException->description_de);
        $this->assertEquals($descriptionEn, $singleEventException->description_en);
    }

    /** @test */
    public function createOrUpdateEventException_timeStartAndEnd_exceptionIsStored() {
        // Arrange
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        $start = $singleEvent->start->clone()->addHour();
        $end = $singleEvent->end->clone()->addHour();

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            null,
            null,
            $start,
            $end,
            null,
            null
        );

        // Assert
        $singleEvent->refresh();

        /** @var SingleEventException $singleEventException */
        $singleEventException = $singleEvent->exception()->first();

        $this->assertNotNull($singleEventException);
        $this->assertEquals($start, $singleEventException->start);
        $this->assertEquals($end, $singleEventException->end);
    }

    /** @test */
    public function createOrUpdateEventException_timeOnlyStartIsSet_exceptionIsStored() {
        // Arrange
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        $start = $singleEvent->start->clone()->subHour();

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            null,
            null,
            $start,
            null,
            null,
            null
        );

        // Assert
        $singleEvent->refresh();

        /** @var SingleEventException $singleEventException */
        $singleEventException = $singleEvent->exception()->first();

        $this->assertNotNull($singleEventException);
        $this->assertEquals($start, $singleEventException->start);
        $this->assertNull($singleEventException->title_de);
        $this->assertNull($singleEventException->title_en);
        $this->assertNull($singleEventException->description_de);
        $this->assertNull($singleEventException->description_en);
        $this->assertNull($singleEventException->end);
    }

    /** @test */
    public function createOrUpdateEventException_timeOnlyEndIsSet_exceptionIsStored() {
        // Arrange
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        $end = $singleEvent->end->clone()->addHour();

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            null,
            null,
            null,
            $end,
            null,
            null
        );

        // Assert
        $singleEvent->refresh();

        /** @var SingleEventException $singleEventException */
        $singleEventException = $singleEvent->exception()->first();

        $this->assertNotNull($singleEventException);
        $this->assertEquals($end, $singleEventException->end);
        $this->assertNull($singleEventException->title_de);
        $this->assertNull($singleEventException->title_en);
        $this->assertNull($singleEventException->description_de);
        $this->assertNull($singleEventException->description_en);
        $this->assertNull($singleEventException->start);
    }

    /** @test */
    public function createOrUpdateEventException_onlyEventLocation_exceptionIsStored() {
        // Arrange
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        /** @var EventLocation $newEventLocation */
        $newEventLocation = EventLocation::factory()->create();

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            null,
            null,
            null,
            null,
            $newEventLocation->guid,
            null
        );

        // Assert
        $singleEvent->refresh();

        /** @var SingleEventException $singleEventException */
        $singleEventException = $singleEvent->exception()->first();

        $this->assertNotNull($singleEventException);

        /** @var EventLocation $eventLocation */
        $eventLocation = $singleEventException->eventLocation()->first();
        $this->assertEquals($newEventLocation->guid, $eventLocation->guid);
        $this->assertNull($singleEventException->title_de);
        $this->assertNull($singleEventException->title_en);
        $this->assertNull($singleEventException->description_de);
        $this->assertNull($singleEventException->description_en);
        $this->assertNull($singleEventException->start);
        $this->assertNull($singleEventException->end);
    }

    /** @test */
    public function createOrUpdateEventException_onlyFileUpload_exceptionIsStored() {
        // Arrange
        /** @var SingleEvent $singleEvent */
        $singleEvent = $this->createSingleEvent()->create();
        /** @var FileUpload $newFileUpload */
        $newFileUpload = FileUpload::factory()->for(User::factory()->create(), 'uploadedBy')->create();

        // Act
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cut->createOrUpdateEventException(
            $singleEvent->guid,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $newFileUpload->guid
        );

        // Assert
        $singleEvent->refresh();

        /** @var SingleEventException $singleEventException */
        $singleEventException = $singleEvent->exception()->first();

        $this->assertNotNull($singleEventException);

        /** @var FileUpload $fileUpload */
        $fileUpload = $singleEventException->fileUpload()->first();
        $this->assertEquals($newFileUpload->guid, $fileUpload->guid);
        $this->assertNull($singleEventException->title_de);
        $this->assertNull($singleEventException->title_en);
        $this->assertNull($singleEventException->description_de);
        $this->assertNull($singleEventException->description_en);
        $this->assertNull($singleEventException->start);
        $this->assertNull($singleEventException->end);
    }

    private static function createSingleEvent(
        ?EventLocation $eventLocation = null,
        ?User          $user = null,
        ?FileUpload    $fileUpload = null
    ): Factory
    {
        $eventLocation = !is_null($eventLocation) ? $eventLocation : EventLocation::factory()->create();
        $user = !is_null($user) ? $user : User::factory()->create();
        $fileUpload = !is_null($fileUpload) ? $fileUpload : FileUpload::factory()->for($user, 'uploadedBy')->create();
        return SingleEvent::factory()
            ->for($eventLocation)
            ->for($fileUpload)
            ->for($user, 'createdBy');
    }
}
