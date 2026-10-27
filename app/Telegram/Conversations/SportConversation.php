<?php

namespace App\Telegram\Conversations;

use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class SportConversation extends Conversation
{
    public ?string $name = null;

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot): void
    {
        $bot->sendMessage('🏅 Привет! Как тебя зовут?');

        $this->next('askName');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function askName(Nutgram $bot): void
    {
        $name = trim($bot->message()->text ?? '');

        if ($name === '') {
            $bot->sendMessage('Пожалуйста, напиши своё имя текстом.');

            return;
        }

        $this->name = $name;

        $bot->sendMessage("Приятно познакомиться, {$this->name}! 💪");

        $this->end();
    }
}
