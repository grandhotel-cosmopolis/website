<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Builder
 * @property string|null $title_de
 * @property string|null $title_en
 * @property string|null $description_de
 * @property string|null $description_en
 * @property Carbon|null $start
 * @property Carbon|null $end
 */
class SingleEventException extends Model
{
    protected $fillable = [
        'title_de',
        'title_en',
        'description_de',
        'description_en',
        'start',
        'end',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function eventLocation(): BelongsTo {
        return $this->belongsTo(EventLocation::class, 'event_location_id');
    }

    public function fileUpload(): BelongsTo {
        return $this->belongsTo(FileUpload::class, 'file_upload_id');
    }

    public function singleEvent(): BelongsTo {
        return $this->belongsTo(SingleEvent::class, 'single_event_id');
    }
}
