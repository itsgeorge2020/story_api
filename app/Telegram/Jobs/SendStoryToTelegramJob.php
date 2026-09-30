<?php

namespace App\Telegram\Jobs;

use App\Enums\StoryStatusEnum;
use App\Models\Story;
use App\Models\TelegramUser;
use App\Telegram\Formatters\TelegramTextFormatter;
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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

#[Queue('telegram')]
#[Tries(3)]
#[Timeout(60)]
#[Backoff([10, 30, 60])]
class SendStoryToTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Story $story,
        public readonly TelegramUser $user,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(
        NutgramFactory $nutgramFactory,
        TelegramTextFormatter $formatter,
    ): void {
        $bot = $nutgramFactory->make();
        $text = $formatter->format($this->story->result_text ?? '');

        foreach ($formatter->splitIntoChunks($text) as $chunk) {
            $bot->sendMessage(
                text: $chunk,
                chat_id: $this->user->chat_id,
                parse_mode: 'HTML',
            );
        }

        $this->story->status = StoryStatusEnum::COMPLETED;
        $this->story->save();
    }
}
