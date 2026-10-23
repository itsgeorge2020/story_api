<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum BotEnum: string
{
    use EnumTrait;

    case STORY = 'SkazkiKotaBot';
    case SPORT = 'SportBot';
}
