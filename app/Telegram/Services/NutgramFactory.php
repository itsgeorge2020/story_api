<?php

namespace App\Telegram\Services;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;

class NutgramFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function make(): Nutgram
    {
        $token = config('nutgram.token');
        $extraConfig = config('nutgram.config', []);

        $config = Configuration::fromArray($extraConfig);

        return new Nutgram($token, $config);
    }
}
