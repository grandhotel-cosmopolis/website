<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function eventLocation(): BelongsTo {
        return $this->belongsTo(EventLocation::class, 'event_location_id');
    }
}
