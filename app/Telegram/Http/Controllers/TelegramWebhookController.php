<?php

namespace App\Telegram\Http\Controllers;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Routing\Controller;

class TelegramWebhookController extends Controller
{
    /**
     * Register with
     * php artisan nutgram:hook:set https://api.storykot.ru/api/v1/telegram/webhook
     */

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(Nutgram $bot): void
    {
        $bot->run();
    }
}
