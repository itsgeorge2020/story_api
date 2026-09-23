<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum StorySourceEnum: string
{
    use EnumTrait;

    case TELEGRAM = 'telegram';
    case APP = 'app';
}
