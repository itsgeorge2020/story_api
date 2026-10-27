<?php

namespace App\Telegram\Http\Controllers;

use App\Enums\BotEnum;
use App\Telegram\Services\NutgramFactory;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TelegramWebhookController extends Controller
{
    /**
     * Register each bot with its own webhook, e.g.
     * php artisan nutgram:hook:set https://api.storykot.ru/api/v1/telegram/webhook/story
     * php artisan nutgram:hook:set https://api.storykot.ru/api/v1/telegram/webhook/sport
     */
    public function __invoke(string $bot, NutgramFactory $nutgramFactory): void
    {
        $botEnum = BotEnum::fromSlug($bot);

        if ($botEnum === null) {
            throw new NotFoundHttpException("Unknown bot [{$bot}]");
        }

        $nutgramFactory->make($botEnum)->run();
    }
}
