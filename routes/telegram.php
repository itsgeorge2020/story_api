<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Conversations\StoryConversation;
use SergiX44\Nutgram\Nutgram;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->onCommand('start', StoryConversation::class);
