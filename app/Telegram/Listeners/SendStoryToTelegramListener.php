<?php

namespace App\Telegram\Listeners;

use App\Events\StoryGeneratedEvent;
use App\Models\TelegramUser;
use App\Telegram\Jobs\SendStoryToTelegramJob;

class SendStoryToTelegramListener
{
    public function handle(StoryGeneratedEvent $event): void
    {
        $story = $event->story;
        $user  = $story->storyable;

        if (!$user instanceof TelegramUser) {
            return;
        }

//        if ($user instanceof TelegramUser) {
//            SendStoryToTelegramJob::dispatch($story, $user)->onQueue('telegram');
//        }

        SendStoryToTelegramJob::dispatch($story, $user);
    }
}
