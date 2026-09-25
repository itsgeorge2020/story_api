<?php

namespace App\Telegram\Listeners;

use App\Events\StoryGeneratedEvent;
use App\Models\TelegramUser;
use App\Telegram\Services\NutgramFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class SendStoryToTelegramListener implements ShouldQueue
{
    public string $queue = 'telegram';

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        private readonly NutgramFactory $nutgramFactory,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(StoryGeneratedEvent $event): void
    {
        $story = $event->story;
        $user  = $story->storyable;

        if (!$user instanceof TelegramUser) {
            return;
        }

        $bot = $this->nutgramFactory->make();

        $text = $story->result_text ?? '';

        foreach (str_split($text, 4000) as $chunk) {
            $bot->sendMessage(
                text: $chunk,
                chat_id: $user->telegram_id,
            );
        }
    }

    public function failed(StoryGeneratedEvent $event, Throwable $exception): void
    {
        Log::channel('generation')->error(
            'SendStoryToTelegramListener failed: ' . $exception->getMessage(),
            ['story_id' => $event->story->id]
        );
    }
}
