<?php

namespace App\Contracts;

use App\DTO\CreateStoryTextExerciseDTO;

interface TextPromptServiceInterface
{
    public function createPrompt(CreateStoryTextExerciseDTO $exercise): string;
}
