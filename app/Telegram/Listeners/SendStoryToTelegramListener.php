<?php

namespace App\Telegram\Listeners;

use App\Events\StoryReadyEvent;
use App\Models\TelegramUser;
use App\Telegram\Jobs\SendStoryToTelegramJob;

class SendStoryToTelegramListener
{
    public function handle(StoryReadyEvent $event): void
    {
        $story = $event->story;
        $user  = $story->storyable;

        if (!$user instanceof TelegramUser) {
            return;
        }

        SendStoryToTelegramJob::dispatch($story, $user);
    }
}
