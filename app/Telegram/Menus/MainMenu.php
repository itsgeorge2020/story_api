<?php

namespace App\Telegram\Menus;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class MainMenu
{
    public const BUTTON_NEW_STORY = '📖 Хочу сказку';

    public static function keyboard(): ReplyKeyboardMarkup
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make(self::BUTTON_NEW_STORY),
            );
    }

    public static function text(): string
    {
        return "😻 Привет! Я помогу сочинить поучительную сказку для твоего ребёнка.\n\n"
            . 'Нажми кнопку ниже, чтобы начать.';
    }
}
