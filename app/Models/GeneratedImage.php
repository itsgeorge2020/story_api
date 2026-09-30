<?php

namespace App\Models;

use App\Enums\GeneratedImageTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GeneratedImage extends Model
{
    private const DISK = 'generated_images';

    protected $fillable = [
        'story_id',
        'type',
        'disk',
        'filename'
    ];

    protected $casts = [
        'type' => GeneratedImageTypeEnum::class,
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->filename);
    }
}
