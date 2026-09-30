<?php

namespace App\Events;

use App\Models\Story;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoryReadyEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Story $story,
    ) {}
}
