<?php

namespace App\Events;

use App\Models\GeneratedImage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImageGeneratedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly GeneratedImage $image,
    ) {}
}
