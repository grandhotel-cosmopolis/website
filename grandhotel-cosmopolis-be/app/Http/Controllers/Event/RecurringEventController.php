<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Http\Dtos\Event\RecurringEventDto;
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
        protected IRecurringEventService $recurringEventService
    ) {}

    /** @noinspection PhpUnused */
    #[OA\Put(
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
                        new OA\Property(property: 'recurrenceMetadata', type: 'integer')
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
    public function create(Request $request): Response | JsonResponse {
        $request->validate([
            'titleDe' => ['required', 'string'],
            'titleEn' => ['required', 'string'],
            'descriptionDe' => ['required', 'string'],
            'descriptionEn' => ['required', 'string'],
            'eventLocationGuid' => ['required', 'string'],
            'fileUploadGuid' => ['required', 'string'],
            'startFirstOccurrence' => ['required', 'date'],
            'endFirstOccurrence' => ['required', 'date'],
            'endRecurrence' => ['date'],
            'recurrence' => ['required', new Enum(Recurrence::class)],
            'recurrenceMetadata' => ['required', 'integer']
        ]);

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
            $request['fileUploadGuid']
        );

        return new JsonResponse(RecurringEventDto::create($newEvent));
    }
}
