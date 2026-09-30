<?php

namespace App\Telegram\Conversations;

use App\Actions\Story\TelegramUserCreateOrUpdateAction;
use App\DTO\ChildDTO;
use App\DTO\CreateStoryTextExerciseDTO;
use App\DTO\TelegramUserDTO;
use App\Enums\StorySourceEnum;
use App\Enums\StoryStatusEnum;
use App\Enums\StoryTypeEnum;
use App\Jobs\GenerateStoryJob;
use App\Models\TelegramUser;
use Illuminate\Support\Arr;
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
    public ?array $lastChildData = null;
    public ?string $requestText = null;

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

        $telegramUser = $this->userCreateOrUpdateAction->handle($telegramUserDTO);

        if (!$telegramUser) {
            $bot->sendMessage('😿 Что-то пошло не так...');
            $this->end();
            return;
        }

        if ($telegramUser->hasDailyLimit()) {
            $bot->sendMessage('Лимит историй на сегодня исчерпан. Возвращайтесь пожалуйста завтра 🌙');
            $this->end();
            return;
        }

        $lastChildData = $this->findLastChildData($telegramUser);

        if ($lastChildData) {
            $this->lastChildData = $lastChildData;

            $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true)
                ->addRow(
                    KeyboardButton::make('Да'),
                    KeyboardButton::make('Нет')
                );

            $bot->sendMessage(
                "Продолжим с «{$lastChildData['name']}»?",
                reply_markup: $keyboard
            );
            $this->next('askContinue');
            return;
        }

        // Нет сохранённого ребёнка — идём по старому сценарию
        $bot->sendMessage('Как зовут ребенка?');
        $this->next('askGender');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function askContinue(Nutgram $bot): void
    {
        $text = $bot->message()->text;

        if (!in_array($text, ['Да', 'Нет'], true)) {
            $bot->sendMessage('Пожалуйста, выбери «Да» или «Нет».');
            return;
        }

        if ($text === 'Нет') {
            // Пользователь хочет ввести нового ребёнка
            $this->lastChildData = null;

            $bot->sendMessage('Как зовут ребенка?',
                reply_markup: ReplyKeyboardRemove::make(true)
            );
            $this->next('askGender');
            return;
        }

        // «Да» — восстанавливаем данные из прошлой истории
        $this->childName = $this->lastChildData['name'];
        $this->age       = $this->lastChildData['age'];
        $this->gender    = $this->lastChildData['gender'];

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make('Решить проблему'),
                KeyboardButton::make('Объяснить')
            );

        $bot->sendMessage('Какую сказку выбираем?', reply_markup: $keyboard);
        $this->next('askRequest');
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
                KeyboardButton::make('Решить проблему'),
                KeyboardButton::make('Объяснить'),
                KeyboardButton::make('Любая сказка')
            );

        $bot->sendMessage('Какую сказку выбираем?', reply_markup: $keyboard);
        $this->next('askRequest');
    }

    // Шаг 5: Спрашиваем текст проблемы
    /**
     * @throws InvalidArgumentException
     */
    public function askRequest(Nutgram $bot): void
    {
        $text = $bot->message()->text;

        $typeMap = [
            'Объяснить'       => StoryTypeEnum::EXPLAIN->value,
            'Решить проблему' => StoryTypeEnum::PROBLEM->value,
            'Любая сказка'    => 'any',
        ];

        if (!isset($typeMap[$text])) {
            $bot->sendMessage('Пожалуйста, выбери один из вариантов кнопками.');
            return;
        }

        $this->storyType = $typeMap[$text];

        if ($this->storyType === 'any') {
            $randomType = StoryTypeEnum::random();
            $this->storyType  = $randomType->value;
            $this->requestText = Arr::random(config("topics.{$this->storyType}"));

            $this->next('generate');
            return;
        }

        $prompts = [
            StoryTypeEnum::EXPLAIN->value => 'Что нужно объяснить? ...',
            StoryTypeEnum::PROBLEM->value => 'Опиши проблему. ...',
        ];

        $bot->sendMessage(
            text: $prompts[$this->storyType],
            reply_markup: ReplyKeyboardRemove::make(true)
        );
        $this->next('askRequestText');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function askRequestText(Nutgram $bot): void
    {
        $text = trim($bot->message()->text ?? '');

        if ($text === '') {
            $bot->sendMessage('Пожалуйста, напиши текстом, что нужно.');
            return;
        }

        if (mb_strlen($text) > 100) {
            $bot->sendMessage('Слишком длинный текст. Попробуй описать короче (до 100 символов).');
            return;
        }

        $this->requestText = $text;

        $this->next('generate');
    }

    // Шаг 6: Генерируем и отправляем (заглушка)
    /**
     * @throws InvalidArgumentException
     */
    public function generate(Nutgram $bot): void
    {
        $child = new ChildDTO(
            name: $this->childName,
            age: $this->age,
            gender: $this->gender
        );

        $exercise = new CreateStoryTextExerciseDTO(
            child: $child,
            requestText: $this->requestText,
            type: StoryTypeEnum::from($this->storyType),
        );

        $telegramUser = TelegramUser::query()
            ->where('telegram_id', $bot->userId())
            ->first();

        if (!$telegramUser) {
            $bot->sendMessage('😿 Что-то пошло не так...');
            $this->end();
            return;
        }

        GenerateStoryJob::dispatch($exercise, $telegramUser, StorySourceEnum::TELEGRAM);

        $bot->sendMessage('🐈 Ищу подходящую сказку...');
        $this->end();
    }

    private function findLastChildData(TelegramUser $user): ?array
    {
        $lastStory = $user->stories()
            ->whereIn('status', [StoryStatusEnum::READY, StoryStatusEnum::COMPLETED])
            ->latest('id')
            ->first();

        return $lastStory?->child_data;
    }
}
