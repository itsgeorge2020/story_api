<?php
/** @var SergiX44\Nutgram\Nutgram $bot */
/** @var App\Enums\BotEnum $botEnum */

use App\Enums\BotEnum;

match ($botEnum) {
    BotEnum::STORY => require __DIR__ . '/telegram/story.php',
    BotEnum::SPORT => require __DIR__ . '/telegram/sport.php',
};
