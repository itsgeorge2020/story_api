<?php

namespace App\Contracts;

interface ImagePromptServiceInterface
{
    public function createPrompt(string $description): string;
}
