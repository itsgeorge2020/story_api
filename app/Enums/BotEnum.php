<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum BotEnum: string
{
    use EnumTrait;

    case STORY = 'SkazkiKotaBot';
    case SPORT = 'SportBot';

    public function slug(): string
    {
        return strtolower($this->name);
    }

    public function token(): ?string
    {
        return config('nutgram.bots.' . $this->slug());
    }

    public static function fromSlug(string $slug): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->slug() === $slug) {
                return $case;
            }
        }

        return null;
    }
}
