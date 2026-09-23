<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum StoryTypeEnum: string
{
    use EnumTrait;

    case TEACH = 'teach';
    case EXPLAIN = 'explain';
    case PROBLEM = 'problem';
}
