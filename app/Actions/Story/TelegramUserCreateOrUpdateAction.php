<?php

namespace App\Actions\Story;

use App\DTO\TelegramUserDTO;
use App\Models\TelegramUser;

class TelegramUserCreateOrUpdateAction
{
    public function handle(TelegramUserDTO $user): TelegramUser
    {
        return TelegramUser::query()->updateOrCreate(
            [
                'telegram_id' => $user->getUserId(),
            ],
            [
                'chat_id'       => $user->getChatId(),
                'first_name'    => $user->getFirstName(),
                'last_name'     => $user->getLastName(),
                'username'      => $user->getUserName(),
                'language_code' => $user->getLanguageCode(),
                'is_premium'    => $user->isPremium() ?? false,
            ]
        );
    }
}
