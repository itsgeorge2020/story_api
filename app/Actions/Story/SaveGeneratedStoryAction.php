<?php

namespace App\Actions\Story;

use App\Contracts\UserInterface;
use App\DTO\CreateStoryTextExerciseDTO;
use App\DTO\GeneratedTextDTO;
use App\Enums\StorySourceEnum;
use App\Enums\StoryStatusEnum;
use App\Models\Story;

class SaveGeneratedStoryAction
{
    public function handle(
        CreateStoryTextExerciseDTO $exercise,
        GeneratedTextDTO $storyData,
        UserInterface $user,
        StorySourceEnum $source = StorySourceEnum::TELEGRAM
    ): Story
    {
        return $user->stories()->create([
            'source'       => $source,
            'type'         => $exercise->getType(),
            'status'       => StoryStatusEnum::PENDING,
            'request_text' => $exercise->getRequestText(),
            'result_text'  => $storyData->getText(),
            'cover_description' => $storyData->getCoverDescription(),
            'child_data'   => $exercise->getChild()->toArray(),
        ]);
    }
}
