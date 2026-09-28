<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Conversations\StoryConversation;
use App\Telegram\Menus\MainMenu;
use SergiX44\Nutgram\Nutgram;

$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage(
        text: MainMenu::text(),
        reply_markup: MainMenu::keyboard(),
    );
})->description('Начало работы');

$bot->onText(MainMenu::BUTTON_NEW_STORY, function (Nutgram $bot) {
    StoryConversation::begin($bot);
});
