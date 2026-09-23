<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    protected $fillable = [
        'telegram_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'is_premium',
        'is_bot'
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'is_premium' => 'boolean',
        'is_bot' => 'boolean',
    ];
}
