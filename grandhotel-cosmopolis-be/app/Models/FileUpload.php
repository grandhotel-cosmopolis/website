<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin Builder
 * @property string $file_path
 * @property string $mime_type
 * @property string $guid
 */
class FileUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'mime_type',
        'guid'
    ];

    public function singleEvents(): HasMany
    {
        return $this->hasMany(SingleEvent::class, 'file_upload_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
