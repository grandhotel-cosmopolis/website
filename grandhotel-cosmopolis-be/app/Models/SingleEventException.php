<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Builder
 * @property Carbon|null $start
 * @property Carbon|null $end
 * @property bool|null $cancelled
 */
class SingleEventException extends Model
{
    protected $fillable = [
        'start',
        'end',
        'cancelled'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'cancelled' => 'boolean'
    ];

    public function eventLocation(): BelongsTo {
        return $this->belongsTo(EventLocation::class, 'event_location_id');
    }

    public function singleEvent(): BelongsTo {
        return $this->belongsTo(SingleEvent::class, 'single_event_id');
    }
}
