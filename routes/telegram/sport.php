<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Conversations\SportConversation;
use SergiX44\Nutgram\Nutgram;

$bot->onCommand('start', function (Nutgram $bot) {
    SportConversation::begin($bot);
})->description('Начало работы');
