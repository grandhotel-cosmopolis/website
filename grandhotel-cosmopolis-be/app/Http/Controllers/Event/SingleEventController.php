<?php

namespace App\Http\Controllers\Event;

use App\Exceptions\InvalidTimeRangeException;
use App\Http\Controllers\Controller;
use App\Http\Dtos\Event\ListSingleEventDto;
use App\Http\Dtos\Event\SingleEventDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Services\Interfaces\IEventService;
use App\Services\Interfaces\ITimeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class SingleEventController extends Controller
{
    public function __construct(
        protected IEventService $eventService,
        protected ITimeService $timeService
    ) {}

    /** @noinspection PhpUnused */
    /**
     * @throws InvalidTimeRangeException
     */
    #[OA\Get(
        path: '/api/singleEvent/list',
        operationId: 'getSingleEvents',
        description: 'get all single events within the specified time range',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'start', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'end', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'all requested events',
                content: new OA\JsonContent(ref: ListSingleEventDto::class)),
            new OA\Response(
                response: 400,
                description: 'invalid time range'
            )
        ])]
    public function getSingleEvents(): JsonResponse | Response {
        if((is_null(request()->query('start')) && !is_null(request()->query('end')))
            || (!is_null(request()->query('start')) && is_null(request()->query('end')))) {
            throw new InvalidTimeRangeException();
        }
        try {
            $start = is_null(request()->query('start')) ? Carbon::now() : Carbon::parse(request()->query('start'));
            $end = is_null(request()->query('end')) ? Carbon::now()->addWeeks(3) : Carbon::parse(request()->query('end'));
        } catch(Exception) {
            throw new InvalidTimeRangeException();
        }
        if (!$this->timeService->validateTimeRange($start, $end)) {
            throw new InvalidTimeRangeException();
        }

        $events = $this->eventService->getSingleEvents($start, $end);

        $eventDtos = $events->map(function (SingleEvent $event) {
            /** @var EventLocation $eventLocation */
            $eventLocation = $event->eventLocation()->first();
            /** @var FileUpload $fileUpload */
            $fileUpload = $event->fileUpload()->first();
            return SingleEventDto::create($event, $eventLocation, $fileUpload);
        });
        return new JsonResponse(new ListSingleEventDto($eventDtos->toArray()));
    }

    /** @noinspection PhpUnused */
    #[OA\Put(
        path: '/api/singleEvent',
        operationId: 'addSingleEvent',
        description: 'Add a new single event',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title_de', type: 'string'),
                        new OA\Property(property: 'title_en', type: 'string'),
                        new OA\Property(property: 'description_de', type: 'string'),
                        new OA\Property(property: 'description_en', type: 'string'),
                        new OA\Property(property: 'start', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'end', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'event_location_guid', type: 'string'),
                        new OA\Property(property: 'file_upload_guid', type: 'string')
                    ]
                )
            )
        ),
        tags: ['Event'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'created event successfully',
                content: new OA\JsonContent(ref: SingleEventDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 422, description: 'validation error'),
            new OA\Response(response: 400, description: 'invalid event')
        ]
    )]
    public function create(Request $request): Response | JsonResponse {
        static::validateSingleEventInput($request);
        $singleEvent = $this->eventService->createSingleEvent(
            $request['title_de'],
            $request['title_en'],
            $request['description_de'],
            $request['description_en'],
            Carbon::parse($request['start']),
            Carbon::parse($request['end']),
            $request['event_location_guid'],
            $request['file_upload_guid']
        );

        return new JsonResponse(SingleEventDto::create($singleEvent));
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/singleEvent/{eventId}/edit',
        operationId: 'editSingleEVent',
        description: 'Edit an existing single event',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title_de', type: 'string'),
                        new OA\Property(property: 'title_en', type: 'string'),
                        new OA\Property(property: 'description_de', type: 'string'),
                        new OA\Property(property: 'description_en', type: 'string'),
                        new OA\Property(property: 'start', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'end', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'event_location_guid', type: 'string'),
                        new OA\Property(property: 'file_upload_guid', type: 'string')
                    ]
                )
            )
        ),
        tags: ['Event'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'edited event successfully',
                content: new OA\JsonContent(ref: SingleEventDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 422, description: 'validation error'),
            new OA\Response(response: 400, description: 'invalid event')
        ]
    )]
    public function edit(Request $request, string $eventId): Response | JsonResponse {
        static::validateSingleEventInput($request);

        $updatedEvent = $this->eventService->updateSingleEvent(
            $eventId,
            $request['title_de'],
            $request['title_en'],
            $request['description_de'],
            $request['description_en'],
            Carbon::parse($request['start']),
            Carbon::parse($request['end']),
            $request['event_location_guid'],
            $request['file_upload_guid']
        );

        return new JsonResponse(SingleEventDto::create($updatedEvent));
    }

    /** @noinspection PhpUnused */
    #[OA\Delete(
        path: '/api/singleEvent/{$eventId}',
        operationId: 'deleteSingleEvent',
        description: 'Delete an existing event',
        tags: ['Event'],
        responses: [
            new OA\Response(response: 200, description: 'deleted event successfully'),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function delete(string $eventId): Response {
        $this->eventService->deleteSingleEvent($eventId);
        return new Response('deleted');
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/singleEvent/{eventId}/publish',
        operationId: 'publishSingleEvent',
        description: 'Publish a single event',
        tags: ['Event'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'published event successfully',
                content: new OA\JsonContent(ref: SingleEventDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function publish(string $eventId): JsonResponse {
        $event = $this->eventService->publishSingleEvent($eventId);
        return new JsonResponse(SingleEventDto::create($event));
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/singleEvent/{eventId}/unpublish',
        operationId: 'unpublishSingleEvent',
        description: 'Unpublish a single event',
        tags: ['Event'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'unpublished event successfully',
                content: new OA\JsonContent(ref: SingleEventDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function unpublish(string $eventId): JsonResponse {
        $event = $this->eventService->unpublishSingleEvent($eventId);
        return new JsonResponse(SingleEventDto::create($event));
    }

    private static function validateSingleEventInput(Request $request): void {
        $request->validate([
            'title_de' => ['required', 'string'],
            'title_en' => ['required', 'string'],
            'description_de' => ['required', 'string'],
            'description_en' => ['required', 'string'],
            'event_location_guid' => ['required', 'string'],
            'file_upload_guid' => ['required', 'string'],
            'start' => ['required' ,'date'],
            'end' => ['required', 'date']
        ]);
    }
}
