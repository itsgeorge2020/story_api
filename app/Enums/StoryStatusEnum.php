<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum StoryStatusEnum: string
{
    use EnumTrait;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case READY = 'ready';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED], true);
    }
}
