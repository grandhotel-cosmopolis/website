<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Dtos\Event\RecurringEventDto;
use App\Models\EventLocation;
use App\Models\FileUpload;
use App\Models\RecurringEvent;
use App\Models\SingleEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

use OpenApi\Attributes as OA;

class RecurringEventController extends Controller
{
    /** @noinspection PhpUnused */
    #[OA\Post(
        path: '/api/recurringEvent/add',
        operationId: 'addRecurringEvent',
        description: 'Add a new recurring event',
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'default_title_de', type: 'string'),
                        new OA\Property(property: 'default_title_en', type: 'string'),
                        new OA\Property(property: 'default_description_de', type: 'string'),
                        new OA\Property(property: 'default_description_en', type: 'string'),
                        new OA\Property(property: 'default_event_location_guid', type: 'string'),
                        new OA\Property(property: 'default_file_upload_guid', type: 'string'),
                        new OA\Property(property: 'start_first_occurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'end_first_occurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'end_recurrence', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'recurrence', ref: Recurrence::class),
                        new OA\Property(property: 'recurrence_metadata', type: 'integer')
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
    public function addRecurringEvent(Request $request): Response | JsonResponse {
        $request->validate([
            'default_title_de' => ['required', 'string'],
            'default_title_en' => ['required', 'string'],
            'default_description_de' => ['required', 'string'],
            'default_description_en' => ['required', 'string'],
            'default_event_location_guid' => ['required', 'string'],
            'default_file_upload_guid' => ['required', 'string'],
            'start_first_occurrence' => ['required', 'date'],
            'end_first_occurrence' => ['required', 'date'],
            'end_recurrence' => ['date'],
            'recurrence' => ['required', new Enum(Recurrence::class)],
            'recurrence_metadata' => ['required', 'integer']
        ]);

        if(!SingleEventController::validateTimeRange($request['start_first_occurrence'], $request['end_first_occurrence'], false)) {
            return response('invalid time range', 422);
        }

        if (!is_null($request['end_recurrence']) && SingleEventController::validateTime($request['end_recurrence'])
            && !SingleEventController::validateTimeRange($request['start_first_occurrence'], $request['end_recurrence'], false)) {
            return response('invalid end_recurrence date', 422);
        }

        $startDateFirstOccurrence = Carbon::parse($request['start_first_occurrence']);
        $endDateFirstOccurrence = Carbon::parse($request['end_first_occurrence']);
        $endDateRecurrence = null;

        if(!is_null($request['end_recurrence']) && SingleEventController::validateTime($request['end_recurrence'])) {
            $endDateRecurrence = Carbon::parse($request['end_recurrence']);
        }

        $newEvent = new RecurringEvent;
        $newEvent->default_title_de = $request['default_title_de'];
        $newEvent->default_title_en = $request['default_title_en'];
        $newEvent->default_description_de = $request['default_description_de'];
        $newEvent->default_description_en = $request['default_description_en'];
        $newEvent->guid = uuid_create();
        $newEvent->start_first_occurrence = $startDateFirstOccurrence;
        $newEvent->end_first_occurrence = $endDateFirstOccurrence;
        $newEvent->end_recurrence = $endDateRecurrence;
        $newEvent->recurrence = $request['recurrence'];
        $newEvent->recurrence_metadata = $request['recurrence_metadata'];

        /** @var EventLocation $eventLocation */
        $eventLocation = EventLocation::query()
            ->where('guid', $request['default_event_location_guid'])
            ->get()
            ->first();

        /** @var FileUpload $fileUpload */
        $fileUpload = FileUpload::query()
            ->where('guid', $request['default_file_upload_guid'])
            ->get()
            ->first();

        /** @var User $user */
        $user = Auth::user();

        $newEvent->defaultFileUpload()->associate($fileUpload);
        $newEvent->defaultEventLocation()->associate($eventLocation);
        $newEvent->createdBy()->associate($user);
        $newEvent->save();

        static::generateSingleEvents(
            $startDateFirstOccurrence,
            $endDateFirstOccurrence,
            $endDateRecurrence,
            $newEvent,
            $eventLocation,
            $fileUpload,
            $user
        );

        return new JsonResponse(RecurringEventDto::create($newEvent, $eventLocation, $fileUpload));
    }

    private static function generateSingleEvents(
        Carbon $startFirstOccurrence,
        Carbon $endFirstOccurrence,
        Carbon | null $endRecurrence,
        RecurringEvent $recurringEvent,
        EventLocation $eventLocation,
        FileUpload $fileUpload,
        User $user
    ): void {
        if (is_null($endRecurrence) || $endRecurrence > Carbon::now()->endOfYear()) {
            $endRecurrence = Carbon::now()->endOfYear();
        }
        $currentStart = $startFirstOccurrence;
        $currentEnd = $endFirstOccurrence;

        while ($currentStart < $endRecurrence) {
            // new single event
            $singleEvent = new SingleEvent;
            $singleEvent->title_de = $recurringEvent->default_title_de;
            $singleEvent->title_en = $recurringEvent->default_title_en;
            $singleEvent->description_de = $recurringEvent->default_description_de;
            $singleEvent->description_en = $recurringEvent->default_description_en;
            $singleEvent->start = $currentStart;
            $singleEvent->end = $currentEnd;
            $singleEvent->guid = uuid_create();
            $singleEvent->is_recurring = true;

            $singleEvent->fileUpload()->associate($fileUpload);
            $singleEvent->eventLocation()->associate($eventLocation);
            $singleEvent->createdBy()->associate($user);
            $singleEvent->recurringEvent()->associate($recurringEvent);
            $singleEvent->save();

            [$currentStart, $currentEnd] = match($recurringEvent->recurrence) {
                Recurrence::EVERY_X_DAYS => self::updateTimesForEveryXDays($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_MONTH_AT_DAY_X => self::updateTimesForEveryMonthAtDayX($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_LAST_DAY_IN_MONTH => self::updateTimesForEveyLastDayInMonth($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_FIRST_DAY_IN_MONTH => self::updateTimesForEveryFirstDayInMonth($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_SECOND_DAY_IN_MONTH => self::updateTimesForEverySecondDayInMonth($currentStart, $currentEnd, $recurringEvent->recurrence_metadata),
                Recurrence::EVERY_THIRD_DAY_IN_MONTH => self::updateTimesForEveryThirdDayInMonth($currentStart, $currentEnd, $recurringEvent->recurrence_metadata)
            };
        }
    }

    /**
     * @return Carbon[]
     */
    private static function updateTimesForEveryXDays(Carbon $startTime, Carbon $endTime, int $numberOfDays): array {
        return [$startTime->addDays($numberOfDays), $endTime->addDays($numberOfDays)];
    }

    /**
     * @return Carbon[]
     */
    private static function updateTimesForEveryMonthAtDayX(Carbon $startTime, Carbon $endTime, int $dayOfMonth): array {
        $clonedStart = $startTime->clone();
        $clonedStart->endOfMonth()->addDay();
        if ($clonedStart->daysInMonth < $dayOfMonth) {
            $startTime->setDay($clonedStart->daysInMonth)->setMonth($clonedStart->month);
        } else {
            $startTime->addMonth();
            $startTime->setDay($dayOfMonth);
        }

        $clonedEnd = $endTime->clone();
        $clonedEnd->endOfMonth()->addDay();
        if($clonedEnd->daysInMonth < $dayOfMonth) {
            $endTime->setDay($clonedStart->daysInMonth)->setMonth($clonedStart->month);
        } else {
            $endTime->addMonth();
            $endTime->setDay($dayOfMonth);
        }

        return [$startTime, $endTime];
    }

    private static function getTime(Carbon $time): array {
        return [$time->hour, $time->minute, $time->second, $time->millisecond];
    }

    /** @return Carbon[] */
    private static function updateTimesForEveyLastDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array {
        [$startHour, $startMinute, $startSecond, $startMillisecond]  = self::getTime($startTime);
        $startTime->endOfMonth()->addDay()->lastOfMonth($dayOfWeek)
            ->setHour($startHour)->setMinute($startMinute)->setSecond($startSecond)->setMillisecond($startMillisecond);
        [$endHour, $endMinute, $endSecond, $endMillisecond] = self::getTime($endTime);
        $endTime->endOfMonth()->addDay()->lastOfMonth($dayOfWeek)
            ->setHour($endHour)->setMinute($endMinute)->setSecond($endSecond)->setMillisecond($endMillisecond);
        return [$startTime, $endTime];
    }

    /** @return Carbon[] */
    private static function updateTimesForEveryFirstDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array {
        [$startHour, $startMinute, $startSecond, $startMillisecond]  = self::getTime($startTime);
        $startTime->addMonth()->firstOfMonth($dayOfWeek)
            ->setHour($startHour)->setMinute($startMinute)->setSecond($startSecond)->setMillisecond($startMillisecond);
        [$endHour, $endMinute, $endSecond, $endMillisecond] = self::getTime($endTime);
        $endTime->addMonth()->firstOfMonth($dayOfWeek)
            ->setHour($endHour)->setMinute($endMinute)->setSecond($endSecond)->setMillisecond($endMillisecond);
        return [$startTime, $endTime];
    }

    /** @return Carbon[] */
    private static function updateTimesForEverySecondDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array {
        [$startHour, $startMinute, $startSecond, $startMillisecond]  = self::getTime($startTime);
        $startTime->addMonth()->nthOfMonth(2, $dayOfWeek)
            ->setHour($startHour)->setMinute($startMinute)->setSecond($startSecond)->setMillisecond($startMillisecond);
        [$endHour, $endMinute, $endSecond, $endMillisecond] = self::getTime($endTime);
        $endTime->addMonth()->nthOfMonth(2, $dayOfWeek)
            ->setHour($endHour)->setMinute($endMinute)->setSecond($endSecond)->setMillisecond($endMillisecond);
        return [$startTime, $endTime];
    }

    /** @return Carbon[] */
    private static function updateTimesForEveryThirdDayInMonth(Carbon $startTime, Carbon $endTime, int $dayOfWeek): array {
        [$startHour, $startMinute, $startSecond, $startMillisecond]  = self::getTime($startTime);
        $startTime->addMonth()->nthOfMonth(3, $dayOfWeek)
            ->setHour($startHour)->setMinute($startMinute)->setSecond($startSecond)->setMillisecond($startMillisecond);
        [$endHour, $endMinute, $endSecond, $endMillisecond] = self::getTime($endTime);
        $endTime->addMonth()->nthOfMonth(3, $dayOfWeek)
            ->setHour($endHour)->setMinute($endMinute)->setSecond($endSecond)->setMillisecond($endMillisecond);
        return [$startTime, $endTime];
    }
}
