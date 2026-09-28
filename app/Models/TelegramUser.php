<?php

namespace App\Models;

use App\Contracts\UserInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TelegramUser extends Model implements UserInterface
{
    private const DAILY_LIMIT = 10;

    protected $fillable = [
        'telegram_id',
        'chat_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'is_premium',
        'is_bot'
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'chat_id' => 'integer',
        'is_premium' => 'boolean',
        'is_bot' => 'boolean',
    ];

    public function stories(): MorphMany
    {
        return $this->morphMany(Story::class, 'storyable');
    }

    public function hasDailyLimit(): bool
    {
        $todayCount = $this->stories()
            ->whereDate('created_at', today())
            ->count();

        return $todayCount >= self::DAILY_LIMIT;
    }
}
