<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'street',
        'city'
    ];

    public function singleEvents(): HasMany
    {
        return $this->hasMany(SingleEvent::class, 'event_location_id');
    }
}
