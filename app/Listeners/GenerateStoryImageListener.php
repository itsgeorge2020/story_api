<?php

namespace App\Listeners;

use App\Events\StoryGeneratedEvent;
use App\Jobs\GenerateStoryImageJob;

class GenerateStoryImageListener
{
    public function handle(StoryGeneratedEvent $event): void
    {
        GenerateStoryImageJob::dispatch($event->story);
    }
}
