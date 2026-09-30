<?php

namespace App\Listeners;

use App\Enums\StoryStatusEnum;
use App\Events\StoryGeneratedEvent;
use App\Events\StoryReadyEvent;

class StoryToReadyListener
{
    public function handle(StoryGeneratedEvent $event): void
    {
        $story = $event->story;

        $story->status = StoryStatusEnum::READY;
        $story->save();

        StoryReadyEvent::dispatch($story);
    }
}
