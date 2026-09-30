<?php

namespace App\Services\GenerateImages;

use App\Contracts\ImagePromptServiceInterface;

class CoverImagePromptService implements ImagePromptServiceInterface
{
    public function createPrompt(string $description): string
    {
        return view('prompts.images.cover', [
            'prompt' => $description,
        ])->render();
    }
}
