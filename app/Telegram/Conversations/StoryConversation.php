<?php

namespace App\Telegram\Conversations;

use App\Models\TelegramUser;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class StoryConversation extends Conversation
{
    // Свойства, которые сохраняются между шагами
    public ?string $childName = null;
    public ?string $gender = null;
    public ?int $age = null;
    public ?string $storyType = null;

    // Шаг 1: Спрашиваем имя

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot): void
    {
        // Получаем данные пользователя из Telegram
        $user = $bot->user();

        // Создаём или обновляем запись (firstOrCreate)
        TelegramUser::updateOrCreate(
            ['telegram_id' => $user->id], // условие поиска
            [
                'first_name'    => $user->first_name,
                'last_name'     => $user->last_name,
                'username'      => $user->username,
                'language_code' => $user->language_code,
                'is_premium'    => $user->is_premium ?? false,
            ]
        );

        $bot->sendMessage('Привет! Как зовут ребенка?');
        $this->next('askGender');
    }

    // Шаг 2: Спрашиваем пол (с кнопками)

    /**
     * @throws InvalidArgumentException
     */
    public function askGender(Nutgram $bot): void
    {
        $this->childName = $bot->message()->text;

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make('Мальчик'),
                KeyboardButton::make('Девочка')
            );

        $bot->sendMessage('Выбери пол:', reply_markup: $keyboard);
        $this->next('askAge');
    }

    // Шаг 3: Спрашиваем возраст

    /**
     * @throws InvalidArgumentException
     */
    public function askAge(Nutgram $bot): void
    {
        $text = $bot->message()->text;

        // Проверяем, что нажата кнопка
        if (!in_array($text, ['Мальчик', 'Девочка'])) {
            $bot->sendMessage('Пожалуйста, выбери кнопку: Мальчик или Девочка');
            return; // Остаёмся на этом же шаге
        }

        $this->gender = $text === 'Мальчик' ? 'boy' : 'girl';

        // Убираем клавиатуру для следующего ввода
        $bot->sendMessage('Сколько лет ребенку? (напиши цифрой)',
            reply_markup: ReplyKeyboardRemove::make(true)
        );
        $this->next('askType');
    }

    // Шаг 4: Спрашиваем тип сказки (с кнопками)

    /**
     * @throws InvalidArgumentException
     */
    public function askType(Nutgram $bot): void
    {
        $text = $bot->message()->text;

        if (!is_numeric($text)) {
            $bot->sendMessage('Пожалуйста, напиши возраст цифрой (например, 6)');
            return;
        }

        $this->age = (int) $text;

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make('Научить'),
                KeyboardButton::make('Объяснить'),
                KeyboardButton::make('Решить проблему')
            );

        $bot->sendMessage('Какую сказку хочешь?', reply_markup: $keyboard);
        $this->next('askProblem');
    }

    // Шаг 5: Спрашиваем текст проблемы

    /**
     * @throws InvalidArgumentException
     */
    public function askProblem(Nutgram $bot): void
    {
        $text = $bot->message()->text;

        $typeMap = [
            'Научить' => 'teach',
            'Объяснить' => 'explain',
            'Решить проблему' => 'problem',
        ];

        if (!isset($typeMap[$text])) {
            $bot->sendMessage('Пожалуйста, выбери один из вариантов кнопками.');
            return;
        }

        $this->storyType = $typeMap[$text];

        $prompts = [
            'teach' => 'Чему нужно научить?',
            'explain' => 'Что нужно объяснить?',
            'problem' => 'Опиши проблему:',
        ];

        $bot->sendMessage($prompts[$this->storyType],
            reply_markup: ReplyKeyboardRemove::make(true)
        );
        $this->next('generate');
    }

    // Шаг 6: Генерируем и отправляем (заглушка)

    /**
     * @throws InvalidArgumentException
     */
    public function generate(Nutgram $bot): void
    {
        $problemText = $bot->message()->text;

        // Пока просто выводим собранные данные для проверки
        $bot->sendMessage(
            "Собрано:\n" .
            "Имя: {$this->childName}\n" .
            "Пол: {$this->gender}\n" .
            "Возраст: {$this->age}\n" .
            "Тип: {$this->storyType}\n" .
            "Проблема: {$problemText}"
        );

        $this->end();
    }
}
