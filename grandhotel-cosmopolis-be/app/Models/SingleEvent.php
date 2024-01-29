<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 * @property string $guid
 * @property string $title_de
 * @property string $title_en
 * @property string $description_de
 * @property string $description_en
 * @property DateTime $start
 * @property DateTime $end
 * @property boolean $is_recurring
 */
class SingleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'guid',
        'title_de',
        'title_en',
        'description_de',
        'description_en',
        'start',
        'end',
        'is_recurring'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'is_recurring' => 'bool'
    ];

    public function eventLocation(): BelongsTo {
        return $this->belongsTo(EventLocation::class, 'event_location_id');
    }

    public function fileUpload(): BelongsTo {
        return $this->belongsTo(FileUpload::class, 'file_upload_id');
    }

    public function createdBy(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recurringEvent(): BelongsTo {
        return $this->belongsTo(RecurringEvent::class, 'recurring_event_id');
    }
}
