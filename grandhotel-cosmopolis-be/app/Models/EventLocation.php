<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin Builder
 *
 * @property $guid
 * @property $name
 * @property $street
 * @property $city
 */
class EventLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'guid',
        'name',
        'street',
        'city'
    ];

    public function singleEvents(): HasMany
    {
        return $this->hasMany(SingleEvent::class, 'event_location_id');
    }
}
