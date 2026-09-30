<?php

namespace App\Contracts;

interface ImageGenerationServiceInterface
{
    public function generateStream(string $prompt, int $steps = 4);
}
