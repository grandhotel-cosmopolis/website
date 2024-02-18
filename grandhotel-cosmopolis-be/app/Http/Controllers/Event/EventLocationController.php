<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Dtos\Event\EventLocationDto;
use App\Http\Dtos\Event\ListEventLocationDto;
use App\Models\EventLocation;
use App\Repositories\Interfaces\IEventLocationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class EventLocationController extends Controller
{
    public function __construct(
        protected IEventLocationRepository $eventLocationRepository
    )
    {
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/eventLocation',
        operationId: 'createEventLocation',
        description: 'Create a new event location',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'street', type: 'string'),
                        new OA\Property(property: 'city', type: 'string'),
                        new OA\Property(property: 'additionalInformation', type: 'string')
                    ]
                )
            )
        ),
        tags: ['EventLocation'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'created EventLocation',
                content: new OA\JsonContent(ref: EventLocationDto::class)
            ),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 422, description: 'input validation error')
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'street' => 'string',
            'city' => 'string',
            'additionalInformation' => 'string'
        ]);

        $newEventLocation = $this->eventLocationRepository->create($request['name'], $request['street'], $request['city'], $request['additionalInformation']);

        return new JsonResponse(EventLocationDto::create($newEventLocation));
    }

    #[OA\Post(
        path: '/api/eventLocation/{eventLocationGuid}/update',
        operationId: 'updateEventLocation',
        description: 'Update an event location',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'street', type: 'string'),
                        new OA\Property(property: 'city', type: 'string')
                    ]
                )
            )
        ),
        tags: ['EventLocation'],
        parameters: [
            new OA\Parameter(name: 'eventLocationGuid', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'updated EventLocation',
                content: new OA\JsonContent(ref: EventLocationDto::class)
            ),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 422, description: 'input validation error')
        ]
    )]
    public function update(Request $request, string $eventLocationGuid): Response|JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'street' => 'string',
            'city' => 'string',
            'additionalInformation' => 'string'
        ]);

        $updatedEventLocation = $this->eventLocationRepository->update($eventLocationGuid, $request['name'], $request['street'], $request['city'], $request['additionalInformation']);

        return new JsonResponse(EventLocationDto::create($updatedEventLocation));
    }

    #[OA\Delete(
        path: '/api/eventLocation/{$eventId}',
        operationId: 'deleteEventLocation',
        description: 'Delete an existing eventLocation',
        tags: ['Event'],
        parameters: [
            new OA\Parameter(name: '$eventId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'deleted event location successfully'),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 404, description: 'not found')
        ]
    )]
    public function delete(string $eventId): Response|JsonResponse
    {
        $this->eventLocationRepository->delete($eventId);
        return new Response('deleted');
    }

    /** @noinspection PhpUnused */
    #[OA\Get(
        path: '/api/eventLocation/list',
        operationId: 'listEventLocations',
        description: 'List Event Locations',
        tags: ['EventLocation'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'list of all event locations',
                content: new OA\JsonContent(ref: ListEventLocationDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated')
        ]
    )]
    public function list(): JsonResponse
    {
        $eventLocations = $this->eventLocationRepository->list();
        $eventLocationDtos = $eventLocations->map(function (EventLocation $eventLocation) {
            return EventLocationDto::create($eventLocation);
        });
        return new JsonResponse(new ListEventLocationDto($eventLocationDtos->toArray()));
    }
}
