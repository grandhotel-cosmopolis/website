<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Dtos\Event\ListSingleEventDto;
use App\Http\Dtos\Event\SingleEventDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\SingleEvent;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class SingleEventController extends Controller
{
    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/singleEvent/add',
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
            new OA\Response(response: 400, description: 'invalid event')
        ]
    )]
    public function addSingleEvent(Request $request): Response | JsonResponse {
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

        if (FileUpload::query()->where('guid', $request['file_upload_guid'])->count() != 1
            || EventLocation::query()->where('guid', $request['event_location_guid'])->count() != 1)
        {
            return response('invalid input', 422);
        }

        $start = $request['start'];
        $end = $request['end'];

        if (!static::validateTimeRange($start, $end, false)) {
            return response('invalid time range', 422);
        }

        $startTime = static::getGivenDateTimeOrDefault($start, Carbon::now());
        $endTime = static::getGivenDateTimeOrDefault($end, Carbon::now());

        $newEvent = new SingleEvent;
        $newEvent->title_de = $request['title_de'];
        $newEvent->title_en = $request['title_en'];
        $newEvent->description_de = $request['description_de'];
        $newEvent->description_en = $request['description_en'];
        $newEvent->start = $startTime;
        $newEvent->end = $endTime;
        $newEvent->guid = uuid_create();

        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::query()
            ->where('guid', $request['event_location_guid'])
            ->get()
            ->first();

        /** @var FileUpload $fileUpload */
        $fileUpload = FileUpload::query()
            ->where('guid', $request['file_upload_guid'])
            ->get()
            ->first();

        /** @var User $user */
        $user = Auth::user();

        $newEvent->fileUpload()->associate($fileUpload);
        $newEvent->eventLocation()->associate($eventLocation);
        $newEvent->createdBy()->associate($user);
        $newEvent->save();

        return new JsonResponse(SingleEventDto::create($newEvent, $eventLocation, $fileUpload));
    }

    /** @noinspection PhpUnused */
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
            })
            ->orderBy('start')
            ->get();

        $eventDtos = $events->map(function (SingleEvent $event) {
            /** @var EventLocation $eventLocation */
            $eventLocation = $event->eventLocation()->first();
            /** @var FileUpload $fileUpload */
            $fileUpload = $event->fileUpload()->first();
            return SingleEventDto::create($event, $eventLocation, $fileUpload);
        });
        return new JsonResponse(new ListSingleEventDto($eventDtos->toArray()));
    }

    public static function validateTimeRange(?string $start, ?string $end, bool $is_null_allowed = true): bool {
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

    public static function validateTime(?string $time): bool {
        try {
            Carbon::parse($time);
        } catch(Exception) {
            return false;
        }
        return true;
    }

    private static function getGivenDateTimeOrDefault(?string $given, DateTime $default): DateTime {
        return is_null($given) ? $default : new Carbon($given);
    }
}
