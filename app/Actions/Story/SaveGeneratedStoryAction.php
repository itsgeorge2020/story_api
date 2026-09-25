<?php

namespace App\Actions\Story;

use App\DTO\CreateStoryTextExerciseDTO;
use App\Enums\StorySourceEnum;
use App\Enums\StoryStatusEnum;
use App\Models\TelegramUser;

class SaveGeneratedStoryAction
{
    public function handle(CreateStoryTextExerciseDTO $exercise, string $text): void
    {
        $tu = TelegramUser::updateOrCreate(
            ['telegram_id' => 123],
            [
                'first_name'    => 'Васян',
                'last_name'     => 'Петрович',
                'username'      => '@vasyAn',
                'language_code' => 'ru',
                'is_premium'    => false,
            ]
        );

        $tu->stories()->create([
            'source'       => StorySourceEnum::TELEGRAM,
            'type'         => $exercise->getType(),
            'status'       => StoryStatusEnum::READY,
            'request_text' => $exercise->getRequestText(),
            'result_text'  => $text,
            'child_data'   => $exercise->getChild()->toArray(),
        ]);
    }
}
