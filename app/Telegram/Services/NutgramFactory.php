<?php

namespace App\Telegram\Services;

use App\Enums\BotEnum;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;

class NutgramFactory
{
    public function make(BotEnum $botEnum): Nutgram
    {
        $extraConfig = config('nutgram.config', []);
        $extraConfig['container'] = app();

        $config = Configuration::fromArray($extraConfig);

        $bot = new Nutgram($botEnum->token(), $config);

        $this->setRunningMode($bot);
        $this->registerHandlers($bot, $botEnum);

        return $bot;
    }

    private function setRunningMode(Nutgram $bot): void
    {
        if (app()->runningInConsole()) {
            $bot->setRunningMode(Polling::class);

            return;
        }

        $webhook = \Nutgram\Laravel\RunningMode\LaravelWebhook::class;

        if (config('nutgram.safe_mode', false)) {
            $webhook = new \Nutgram\Laravel\RunningMode\LaravelWebhook(
                getToken: fn () => request()?->header('X-Telegram-Bot-Api-Secret-Token'),
                secretToken: md5(config('app.key')),
            );
            $webhook->setSafeMode(true);
        }

        $bot->setRunningMode($webhook);
    }

    private function registerHandlers(Nutgram $bot, BotEnum $botEnum): void
    {
        $path = base_path('routes/telegram.php');

        if (! file_exists($path)) {
            return;
        }

        (function () use ($bot, $botEnum, $path) {
            require $path;
        })();
    }
}
