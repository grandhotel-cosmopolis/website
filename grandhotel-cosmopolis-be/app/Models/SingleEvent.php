<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 * @property string $title_de
 * @property string $title_en
 * @property string $description_de
 * @property string $description_en
 * @property DateTime $start
 * @property DateTime $end
 * @property string $imageUrl
 */
class SingleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_de',
        'title_en',
        'description_de',
        'description_en',
        'start',
        'end',
        'image_url'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime'
    ];

    public function eventLocation(): BelongsTo {
        return $this->belongsTo(EventLocation::class, 'event_location_id');
    }
}
