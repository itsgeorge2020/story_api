<?php

namespace App\Models;

use App\Enums\StorySourceEnum;
use App\Enums\StoryStatusEnum;
use App\Enums\StoryTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Story extends Model
{
    protected $fillable = [
        'source',
        'type',
        'status',
        'request_text',
        'result_text',
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
}
