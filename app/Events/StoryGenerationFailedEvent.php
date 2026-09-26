<?php

namespace App\Events;

use App\Contracts\UserInterface;
use App\Enums\StorySourceEnum;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoryGenerationFailedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly UserInterface $user,
        public readonly StorySourceEnum $source,
    ) {}
}
