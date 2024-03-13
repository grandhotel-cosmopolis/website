<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Dtos\Event\ListRecurringEventDto;
use App\Http\Dtos\Event\ListSingleEventDto;
use App\Http\Dtos\Event\RecurringEventDto;
use App\Http\Dtos\Event\SingleEventDto;
use App\Http\Rules\ValidRecurrenceMetadata;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\SingleEvent;
use App\Repositories\Interfaces\IRecurringEventRepository;
use App\Repositories\Interfaces\ISingleEventRepository;
use App\Services\Interfaces\IRecurringEventService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

use OpenApi\Attributes as OA;

class RecurringEventController extends Controller
{

    public function __construct(
        protected IRecurringEventService    $recurringEventService,
        protected IRecurringEventRepository $eventRepository,
        protected ISingleEventRepository    $singleEventRepository
    )
    {
    }

    /** @noinspection PhpUnused */
    #[OA\Get(
        path: '/api/recurringEvent/listAll',
        operationId: 'getAllRecurringEvents',
        description: 'list all recurring events',
        tags: ['Event'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'created recurring event successfully',
                content: new OA\JsonContent(ref: ListRecurringEventDto::class)
            )
        ]
    )]
    public function listAll(): JsonResponse
    {
        $events = $this->eventRepository->listAll();

        $eventDtos = $events->map(function (RecurringEvent $event) {
            /** @var EventLocation $eventLocation */
            $eventLocation = $event->eventLocation()->first();
            /** @var FileUpload $fileUpload */
            $fileUpload = $event->fileUpload()->first();
            return RecurringEventDto::create($event, $eventLocation, $fileUpload);
        });
        return new JsonResponse(new ListRecurringEventDto($eventDtos->toArray()));
    }

    /** @noinspection PhpUnused */
    #[OA\Get(
        path: '/api/recurringEvent/{eventGuid}/listSingleEvents',
        operationId: 'getSingleEventsByRecurringEventGuid',
        description: 'list all single events for a given recurring event guid',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'eventGuid', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'list of all single events of the given recurring event',
                content: new OA\JsonContent(ref: ListSingleEventDto::class)
            )
        ]
    )]
    public function listAllSingleEventsByRecurringEventId(string $eventGuid): JsonResponse
    {
        $events = $this->singleEventRepository->listAllByRecurringEventGuid($eventGuid);
        $eventDtos = $events->map(function (SingleEvent $event) {
            return SingleEventDto::create($event);
        });
        return new JsonResponse(new ListSingleEventDto($eventDtos->toArray()));
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/recurringEvent',
        operationId: 'createRecurringEvent',
        description: 'Create a new recurring event',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'titleDe', type: 'string'),
                        new OA\Property(property: 'titleEn', type: 'string'),
                        new OA\Property(property: 'descriptionDe', type: 'string'),
                        new OA\Property(property: 'descriptionEn', type: 'string'),
                        new OA\Property(property: 'eventLocationGuid', type: 'string'),
                        new OA\Property(property: 'fileUploadGuid', type: 'string'),
                        new OA\Property(property: 'startFirstOccurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'endFirstOccurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'endRecurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'recurrence', ref: Recurrence::class),
                        new OA\Property(property: 'recurrenceMetadata', type: 'integer'),
                        new OA\Property(property: 'isPublic', type: 'boolean'),
                    ]
                )
            )
        ),
        tags: ['Event'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'created recurring event successfully',
                content: new OA\JsonContent(ref: RecurringEventDto::class)
            ),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 422, description: 'validation errors')
        ]
    )]
    public function create(Request $request): Response|JsonResponse
    {
        static::validateRecurringEventInput($request);

        $isPublic = $request['isPublic'] == 'true';

        $newEvent = $this->recurringEventService->create(
            $request['titleDe'],
            $request['titleEn'],
            $request['descriptionDe'],
            $request['descriptionEn'],
            Carbon::parse($request['startFirstOccurrence']),
            Carbon::parse($request['endFirstOccurrence']),
            is_null($request['endRecurrence']) ? null : Carbon::parse($request['endRecurrence']),
            Recurrence::from($request['recurrence']),
            $request['recurrenceMetadata'],
            $request['eventLocationGuid'],
            $request['fileUploadGuid'],
            $isPublic
        );

        return new JsonResponse(RecurringEventDto::create($newEvent));
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/recurringEvent/{eventGuid}/update',
        operationId: 'updateRecurringEvent',
        description: 'Update a recurring event',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'titleDe', type: 'string'),
                        new OA\Property(property: 'titleEn', type: 'string'),
                        new OA\Property(property: 'descriptionDe', type: 'string'),
                        new OA\Property(property: 'descriptionEn', type: 'string'),
                        new OA\Property(property: 'eventLocationGuid', type: 'string'),
                        new OA\Property(property: 'fileUploadGuid', type: 'string'),
                        new OA\Property(property: 'startFirstOccurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'endFirstOccurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'endRecurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'recurrence', ref: Recurrence::class),
                        new OA\Property(property: 'recurrenceMetadata', type: 'integer'),
                        new OA\Property(property: 'isPublic', type: 'boolean')
                    ]
                )
            )
        ),
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'eventGuid', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'update recurring event successfully',
                content: new OA\JsonContent(ref: RecurringEventDto::class)
            ),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 422, description: 'validation errors')
        ]
    )]
    public function update(Request $request, string $eventGuid): Response|JsonResponse
    {
        static::validateRecurringEventInput($request);

        $isPublic = $request['isPublic'] == 'true';

        $newEvent = $this->recurringEventService->update(
            $eventGuid,
            $request['titleDe'],
            $request['titleEn'],
            $request['descriptionDe'],
            $request['descriptionEn'],
            Carbon::parse($request['startFirstOccurrence']),
            Carbon::parse($request['endFirstOccurrence']),
            is_null($request['endRecurrence']) ? null : Carbon::parse($request['endRecurrence']),
            Recurrence::from($request['recurrence']),
            $request['recurrenceMetadata'],
            $request['eventLocationGuid'],
            $request['fileUploadGuid'],
            $isPublic
        );

        return new JsonResponse(RecurringEventDto::create($newEvent));
    }

    #[OA\Delete(
        path: '/api/recurringEvent/{eventGuid}',
        operationId: 'deleteRecurringEvent',
        description: 'Delete an existing recurring event with all its single events',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'eventGuid', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'deleted event successfully'),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function delete(string $eventGuid): Response
    {
        $this->recurringEventService->delete($eventGuid);
        return new Response('deleted');
    }

    #[OA\Post(
        path: '/api/recurringEvent/{eventGuid}/publish',
        operationId: 'publishRecurringEvent',
        description: 'Publish a recurring event and all its single events.',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'eventGuid', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'published event successfully',
                content: new OA\JsonContent(ref: RecurringEventDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function publish(string $eventGuid): JsonResponse
    {
        $event = $this->recurringEventService->publish($eventGuid);
        return new JsonResponse(RecurringEventDto::create($event));
    }

    #[OA\Post(
        path: '/api/recurringEvent/{eventGuid}/unpublish',
        operationId: 'unpublishRecurringEvent',
        description: 'unpublishes a recurring event and all its single events',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'eventGuid', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'unpublished event successfully',
                content: new OA\JsonContent(ref: RecurringEventDto::class)
            ),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function unpublish(string $eventGuid): JsonResponse
    {
        $event = $this->recurringEventService->unpublish($eventGuid);
        return new JsonResponse(RecurringEventDto::create($event));
    }

    private static function validateRecurringEventInput(Request $request): void
    {
        $request->validate([
            'titleDe' => ['required', 'string'],
            'titleEn' => ['required', 'string'],
            'descriptionDe' => ['required', 'string'],
            'descriptionEn' => ['required', 'string'],
            'eventLocationGuid' => ['required', 'string'],
            'fileUploadGuid' => ['required', 'string'],
            'startFirstOccurrence' => ['required', 'date', 'after_or_equal:today'],
            'endFirstOccurrence' => ['required', 'date'],
            'endRecurrence' => ['date'],
            'recurrence' => ['required', new Enum(Recurrence::class)],
            'recurrenceMetadata' => ['required', 'numeric', new ValidRecurrenceMetadata($request['recurrence'])]
        ]);
    }
}
