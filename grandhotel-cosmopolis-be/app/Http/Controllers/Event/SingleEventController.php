<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\EventLocation;
use App\Models\SingleEvent;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use TypeError;

class SingleEventController extends Controller
{
    #[OA\Post(
        path: '/api/singleEvents/add',
        operationId: 'addSingleEvent',
        description: 'Add a new single event',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(ref: CreateSingleEventRequestDto::class)
        ),
        tags: ['Event'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'created event successfully',
                content: new OA\JsonContent(ref: SingleEventDto::class)
            ),
            new OA\Response(response: 401, description: 'unauthenticated'),
            new OA\Response(response: 400, description: 'invalid event')
        ]
    )]
    public function addSingleEvent(): Response | JsonResponse {
        try {
            $title_de = static::getJsonValue('title_de');
            $title_en = static::getJsonValue('title_en');
            $description_de = static::getJsonValue('description_de');
            $description_en = static::getJsonValue('description_en');
            $eventLocation_name = static::getJsonValue('eventLocation.name');
            $eventLocation_street = static::getJsonValue('eventLocation.street');
            $eventLocation_city = static::getJsonValue('eventLocation.city');
            $start = static::getJsonValue('start');
            $end = static::getJsonValue('end');
        } catch (TypeError) {
            return response('corrupt input', 400);
        }

        if(
            is_null($title_de)
            || is_null($title_en)
            || is_null($description_de)
            || is_null($description_en)
            || is_null($eventLocation_name)
        )
        {
            return response('invalid input', 400);
        }

        if (!static::validateTimeRange($start, $end, false)) {
            return response('invalid time range', 400);
        }

        $startTime = static::getGivenDateTimeOrDefault($start, Carbon::now());
        $endTime = static::getGivenDateTimeOrDefault($end, Carbon::now());

        /** @var EventLocation|null $existingEventLocation
         */
        $existingEventLocation = EventLocation::query()
            ->where('name', '=', $eventLocation_name)
            ->where('street', '=', $eventLocation_street)
            ->firstWhere('city', '=', $eventLocation_city);

        $newEvent = new SingleEvent;
        $newEvent->title_de = $title_de;
        $newEvent->title_en = $title_en;
        $newEvent->description_de = $description_de;
        $newEvent->description_en = $description_en;
        $newEvent->start = $startTime;
        $newEvent->end = $endTime;


        if (is_null($existingEventLocation)) {
            $newEventLocation = new EventLocation;
            $newEventLocation->name = $eventLocation_name;
            $newEventLocation->street = $eventLocation_street;
            $newEventLocation->city = $eventLocation_city;
            $newEventLocation->save();
            $existingEventLocation = $newEventLocation;
        }
        $existingEventLocation->singleEvents()->save($newEvent);
        $eventLocation = $existingEventLocation;

        $responseObject = new SingleEventDto(
            $newEvent->title_de,
            $newEvent->title_en,
            $newEvent->description_de,
            $newEvent->description_en,
            new EventLocationDto(
                $eventLocation->name,
                $eventLocation->street,
                $eventLocation->city
            ),
            $newEvent->start,
            $newEvent->end,
            $newEvent->imageUrl
        );

        return new JsonResponse($responseObject);
    }

    #[OA\Get(
        path: '/api/singleEvents/list',
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
        $start = request()->query('start');
        $end = request()->query('end');

        if (!static::validateTimeRange($start, $end)) {
            return response([], 400);
        }

        $start = static::getGivenDateTimeOrDefault($start, Carbon::now());
        $end = static::getGivenDateTimeOrDefault($end, Carbon::now()->addWeeks(3));

        $events = SingleEvent::query()
            ->where(function (Builder $query) use ($start, $end) {
                $query
                    ->where('start', '>', $start)
                    ->where('end', '<', $end);
            })
            ->orWhere(function (Builder $query) use ($start) {
                $query
                    ->where('start', '<', $start)
                    ->where('end', '>', $start);
            })
            ->orWhere(function (Builder $query) use ($end) {
                $query
                    ->where('end', '>', $end)
                    ->where('start', '<', $end);
            })->get();

        $eventDtos = $events->map(function (SingleEvent $event) {
            /** @var EventLocation $eventLocation */
            $eventLocation = $event->eventLocation()->first();
            return new SingleEventDto(
                $event->title_de,
                $event->title_en,
                $event->description_de,
                $event->description_en,
                new EventLocationDto(
                    $eventLocation->name,
                    $eventLocation->street,
                    $eventLocation->city
                ),
                $event->start,
                $event->end,
                $event->imageUrl
            );
        });
        return new JsonResponse(new ListSingleEventDto($eventDtos->toArray()));
    }

    private static function getJsonValue(string $key): ?string {
        return request()->input($key);
    }

    private static function validateTimeRange(?string $start, ?string $end, bool $is_null_allowed = true): bool {
        if ($is_null_allowed && is_null($start) && is_null($end)) {
            return true;
        }

        if ((is_null($start) && !is_null($end)) || (!is_null($start) && is_null($end))) {
            return false;
        }

        try {
            $startDate = new Carbon($start);
            $endDate = new Carbon($end);
        } catch (Exception) {
            return false;
        }

        if ($startDate > $endDate) {
            return false;
        }

        return true;
    }

    private static function getGivenDateTimeOrDefault(?string $given, DateTime $default): DateTime {
        return is_null($given) ? $default : new Carbon($given);
    }
}
