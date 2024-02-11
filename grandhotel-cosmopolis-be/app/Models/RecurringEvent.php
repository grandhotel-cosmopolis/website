<?php

namespace App\Models;

use App\Http\Controllers\Event\Recurrence;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin Builder
 * @property string $guid
 * @property string $title_de
 * @property string $title_en
 * @property string $description_de
 * @property string $description_en
 * @property Recurrence $recurrence
 * @property int $recurrence_metadata
 * @property Carbon $start_first_occurrence
 * @property Carbon $end_first_occurrence
 * @property Carbon | null $end_recurrence
 * @property bool $is_public
 */
class RecurringEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'guid',
        'title_de',
        'title_en',
        'description_de',
        'description_en',
        'recurrence',
        'recurrence_metadata',
        'start_first_occurrence',
        'end_first_occurrence',
        'end_recurrence',
        'is_public'
    ];

    protected $casts = [
        'start_first_occurrence' => 'datetime',
        'end_first_occurrence' => 'datetime',
        'end_recurrence' => 'datetime',
        'recurrence' => Recurrence::class,
        'is_public' => 'boolean'
    ];

    public function eventLocation(): BelongsTo
    {
        return $this->belongsTo(EventLocation::class, 'event_location_id');
    }

    public function fileUpload(): BelongsTo
    {
        return $this->belongsTo(FileUpload::class, 'file_upload_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function singleEvents(): HasMany
    {
        return $this->hasMany(SingleEvent::class, 'recurring_event_id');
    }
}
