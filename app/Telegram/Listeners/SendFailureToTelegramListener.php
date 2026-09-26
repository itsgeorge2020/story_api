<?php

namespace App\Telegram\Listeners;

use App\Events\StoryGenerationFailedEvent;
use App\Models\TelegramUser;
use App\Telegram\Jobs\SendFailureToTelegramJob;

class SendFailureToTelegramListener
{
    public function handle(StoryGenerationFailedEvent $event): void
    {
        $user = $event->user;

        if (!$user instanceof TelegramUser) {
            return;
        }

        SendFailureToTelegramJob::dispatch($user);
    }
}
