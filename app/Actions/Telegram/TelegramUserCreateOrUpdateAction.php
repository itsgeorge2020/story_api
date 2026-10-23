<?php

namespace App\Actions\Telegram;

use App\DTO\TelegramUserDTO;
use App\Models\TelegramUser;

class TelegramUserCreateOrUpdateAction
{
    public function handle(TelegramUserDTO $user): ?TelegramUser
    {
        $telegramUser = TelegramUser::query()->firstOrNew([
            'telegram_id' => $user->getUserId(),
            'from_bot'    => $user->getFromBot(),
        ]);

        $telegramUser->fill([
            'chat_id'       => $user->getChatId(),
            'first_name'    => $user->getFirstName(),
            'last_name'     => $user->getLastName(),
            'username'      => $user->getUserName(),
            'language_code' => $user->getLanguageCode(),
            'is_premium'    => $user->isPremium() ?? false,
        ]);

        $telegramUser->save();

        return $telegramUser;
    }
}
