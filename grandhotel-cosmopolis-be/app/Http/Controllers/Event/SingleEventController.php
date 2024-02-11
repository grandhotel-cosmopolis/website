<?php

namespace App\Http\Controllers\Event;

use App\Exceptions\InvalidTimeRangeException;
use App\Http\Controllers\Controller;
use App\Http\Dtos\Event\ListSingleEventDto;
use App\Http\Dtos\Event\SingleEventDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Services\Interfaces\ISingleEventService;
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
        protected ISingleEventService $eventService,
        protected ITimeService        $timeService
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

        $events = $this->eventService->list($start, $end);

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
                        new OA\Property(property: 'titleDe', type: 'string'),
                        new OA\Property(property: 'titleEn', type: 'string'),
                        new OA\Property(property: 'descriptionDe', type: 'string'),
                        new OA\Property(property: 'descriptionEn', type: 'string'),
                        new OA\Property(property: 'start', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'end', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'eventLocationGuid', type: 'string'),
                        new OA\Property(property: 'fileUploadGuid', type: 'string')
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
        $singleEvent = $this->eventService->create(
            $request['titleDe'],
            $request['titleEn'],
            $request['descriptionDe'],
            $request['descriptionEn'],
            Carbon::parse($request['start']),
            Carbon::parse($request['end']),
            $request['eventLocationGuid'],
            $request['fileUploadGuid']
        );

        return new JsonResponse(SingleEventDto::create($singleEvent));
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/singleEvent/{eventGuid}/update',
        operationId: 'editSingleEVent',
        description: 'Edit an existing single event',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'titleDe', type: 'string'),
                        new OA\Property(property: 'titleEn', type: 'string'),
                        new OA\Property(property: 'descriptionDe', type: 'string'),
                        new OA\Property(property: 'descriptionEn', type: 'string'),
                        new OA\Property(property: 'start', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'end', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'eventLocationGuid', type: 'string'),
                        new OA\Property(property: 'fileUploadGuid', type: 'string')
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
                description: 'edited event successfully',
                content: new OA\JsonContent(ref: SingleEventDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 422, description: 'validation error'),
            new OA\Response(response: 400, description: 'invalid event')
        ]
    )]
    public function update(Request $request, string $eventGuid): Response | JsonResponse {
        static::validateSingleEventInput($request);

        $updatedEvent = $this->eventService->update(
            $eventGuid,
            $request['titleDe'],
            $request['titleEn'],
            $request['descriptionDe'],
            $request['descriptionEn'],
            Carbon::parse($request['start']),
            Carbon::parse($request['end']),
            $request['eventLocationGuid'],
            $request['fileUploadGuid']
        );

        return new JsonResponse(SingleEventDto::create($updatedEvent));
    }

    /** @noinspection PhpUnused */
    #[OA\Delete(
        path: '/api/singleEvent/{eventGuid}',
        operationId: 'deleteSingleEvent',
        description: 'Delete an existing event',
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
    public function delete(string $eventGuid): Response {
        $this->eventService->delete($eventGuid);
        return new Response('deleted');
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/singleEvent/{eventGuid}/publish',
        operationId: 'publishSingleEvent',
        description: 'Publish a single event',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'eventGuid', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'published event successfully',
                content: new OA\JsonContent(ref: SingleEventDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function publish(string $eventGuid): JsonResponse {
        $event = $this->eventService->publish($eventGuid);
        return new JsonResponse(SingleEventDto::create($event));
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/singleEvent/{eventGuid}/unpublish',
        operationId: 'unpublishSingleEvent',
        description: 'Unpublish a single event',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: 'eventGuid', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'unpublished event successfully',
                content: new OA\JsonContent(ref: SingleEventDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function unpublish(string $eventGuid): JsonResponse {
        $event = $this->eventService->unpublish($eventGuid);
        return new JsonResponse(SingleEventDto::create($event));
    }

    private static function validateSingleEventInput(Request $request): void {
        $request->validate([
            'titleDe' => ['required', 'string'],
            'titleEn' => ['required', 'string'],
            'descriptionDe' => ['required', 'string'],
            'descriptionEn' => ['required', 'string'],
            'eventLocationGuid' => ['required', 'string'],
            'fileUploadGuid' => ['required', 'string'],
            'start' => ['required' ,'date'],
            'end' => ['required', 'date']
        ]);
    }
}
