<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Dtos\Event\EventLocationDto;
use App\Http\Dtos\Event\ListEventLocationDto;
use App\Models\EventLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class EventLocationController extends Controller
{
    /** @noinspection PhpUnused */
    #[OA\Get(
        path: 'api/eventLocation/list',
        operationId: 'listEventLocations',
        description: 'List Event Locations',
        tags: ['EventLocation'],
        responses: [
            new OA\Response(response: 200, description: 'list of all event locations', content: new OA\JsonContent(ref: ListEventLocationDto::class)),
            new OA\Response(response: 401, description: 'unauthenticated')
        ]
    )]
    public function listEventLocations(): JsonResponse {
        $eventLocations = EventLocation::all();
        $eventLocationDtos = $eventLocations->map(function (EventLocation $eventLocation) {
            return EventLocationDto::create($eventLocation);
        });
        return new JsonResponse(new ListEventLocationDto($eventLocationDtos->toArray()));
    }

    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/eventLocation/add',
        operationId: 'addEventLocation',
        description: 'Add a new event location',
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
        )
    )]
    public function addEventLocation(Request $request): JsonResponse {
        $request->validate([
            'name' => ['required', 'string'],
            'street' => 'string',
            'city' => 'string'
        ]);

        $newEventLocation = new EventLocation;
        $newEventLocation->name = $request['name'];
        $newEventLocation->street = $request['street'];
        $newEventLocation->city = $request['city'];
        $newEventLocation->guid = uuid_create();
        $newEventLocation->save();

        return new JsonResponse(EventLocationDto::create($newEventLocation));
    }
}
