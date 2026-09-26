<?php

namespace App\Telegram\Conversations;

use App\Actions\Story\TelegramUserCreateOrUpdateAction;
use App\DTO\ChildDTO;
use App\DTO\CreateStoryTextExerciseDTO;
use App\DTO\TelegramUserDTO;
use App\Enums\StorySourceEnum;
use App\Enums\StoryTypeEnum;
use App\Jobs\GenerateStoryJob;
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
    private ?TelegramUser $telegramUser = null;

    public function __construct(
        private readonly TelegramUserCreateOrUpdateAction $userCreateOrUpdateAction
    ) {}

    // Шаг 1: Спрашиваем имя
    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot): void
    {
        $user = $bot->user();

        $telegramUserDTO = new TelegramUserDTO(
            userId: $bot->userId(),
            chatId: $bot->chatId(),
            userName: $user->username,
            languageCode: $user->language_code,
            isPremium: $user->is_premium ?? false,
            firstName: $user->first_name,
            lastName: $user->last_name
        );

        $this->telegramUser = $this->userCreateOrUpdateAction->handle($telegramUserDTO);

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

        $child = new ChildDTO(
            name: $this->childName,
            age: $this->age,
            gender: $this->gender
        );

        $exercise = new CreateStoryTextExerciseDTO(
            child: $child,requestText:
            $problemText,type:
            StoryTypeEnum::getByCase($this->storyType)
        );

        if (!$this->telegramUser) {
            $bot->sendMessage('Что-то пошло не так...');
            $this->end();
        }

        GenerateStoryJob::dispatch($exercise, $this->telegramUser, StorySourceEnum::TELEGRAM);

        $bot->sendMessage('Ищу подходящую сказку...');
        $this->end();
    }
}
