<?php

namespace App\Http\Controllers\Event;

use App\Models\EventLocation;
use App\Models\SingleEvent;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class SingleEventController
{

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
                content: new OA\JsonContent(ref: ListSingleEventDto::class))
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

    private static function validateTimeRange(?string $start, ?string $end): bool {
        if (is_null($start) && is_null($end)) {
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
