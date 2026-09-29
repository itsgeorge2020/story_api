<?php

namespace App\Services\GenerateTexts;

use App\Contracts\TextPromptServiceInterface;
use App\DTO\CreateStoryTextExerciseDTO;
use App\Enums\StoryTypeEnum;

class DeepSeekTextPromptService implements TextPromptServiceInterface
{
    public function createPrompt(CreateStoryTextExerciseDTO $exercise): string
    {
        $template = $this->getTemplate($exercise->getType());

        return view($template, [
            'gender'  => $exercise->getChild()->getGender() === 'boy' ? 'мальчик' : 'девочка',
            'name'    => $exercise->getChild()->getName(),
            'age' => $exercise->getChild()->getAge(),
            'request' => $exercise->getRequestText(),
        ])->render();
    }

    private function getTemplate(StoryTypeEnum $type): string
    {
        return match ($type) {
            StoryTypeEnum::TEACH => 'prompts.text_teach',
            StoryTypeEnum::EXPLAIN => 'prompts.text_explain',
            StoryTypeEnum::PROBLEM => 'prompts.text_problem'
        };
    }
}
