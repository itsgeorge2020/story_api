<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum StoryTypeEnum: string
{
    use EnumTrait;

    case TEACH = 'teach';
    case EXPLAIN = 'explain';
    case PROBLEM = 'problem';

    public function label(): string
    {
        return match ($this) {
            self::TEACH => 'Научить',
            self::EXPLAIN => 'Объяснить',
            self::PROBLEM => 'Решить проблему',
        };
    }
}
