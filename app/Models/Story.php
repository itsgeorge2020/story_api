<?php

namespace App\Models;

use App\Enums\GeneratedImageTypeEnum;
use App\Enums\StorySourceEnum;
use App\Enums\StoryStatusEnum;
use App\Enums\StoryTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Story extends Model
{
    protected $fillable = [
        'source',
        'type',
        'status',
        'request_text',
        'result_text',
        'cover_description',
        'child_data',
        'story',
    ];

    protected $casts = [
        'source' => StorySourceEnum::class,
        'type' => StoryTypeEnum::class,
        'status' => StoryStatusEnum::class,
        'child_data' => 'array',
    ];

    public function storyable(): MorphTo
    {
        return $this->morphTo();
    }

    public function generatedImages(): HasMany
    {
        return $this->hasMany(GeneratedImage::class);
    }

    public function cover(): HasOne
    {
        return $this->hasOne(GeneratedImage::class)
            ->where('type', GeneratedImageTypeEnum::COVER);
    }
}
