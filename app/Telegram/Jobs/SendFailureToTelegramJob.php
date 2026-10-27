<?php

namespace App\Telegram\Jobs;

use App\Models\TelegramUser;
use App\Telegram\Services\NutgramFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

#[Queue('telegram')]
#[Tries(3)]
#[Timeout(60)]
#[Backoff([10, 30, 60])]
class SendFailureToTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const FAILURE_MESSAGE = '😔 Не получилось сочинить сказку. Попробуй ещё раз через пару минут.';

    public function __construct(
        public readonly TelegramUser $user,
    ) {}

    public function handle(NutgramFactory $nutgramFactory): void
    {
        $bot = $nutgramFactory->make($this->user->from_bot);

        $bot->sendMessage(
            text: self::FAILURE_MESSAGE,
            chat_id: $this->user->chat_id,
        );
    }
}
