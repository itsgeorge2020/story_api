<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\TextPromptServiceInterface;
use App\Contracts\TextRequestServiceInterface;
use App\DTO\CreateStoryTextExerciseDTO;
use App\Enums\StorySourceEnum;
use App\Enums\StoryStatusEnum;
use App\Exceptions\DeepSeekException;
use App\Exceptions\GenerateException;
use App\Http\Requests\Api\V1\StoryCreateRequest;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoryController extends BaseController
{
    public function __construct(
        private readonly TextRequestServiceInterface $textRequestService,
        private readonly TextPromptServiceInterface $textPromptService
    ) {}

    public function store(StoryCreateRequest $request): JsonResponse
    {
        try {
            $exercise = CreateStoryTextExerciseDTO::createFromRequest($request);
            $prompt = $this->textPromptService->createPrompt($exercise);

            $story = $this->textRequestService->send($prompt);
            $this->saveStory($exercise, $story);
        } catch (DeepSeekException $exception) {
            Log::channel('deepseekApi')->error($exception->getMessage());
            return $this->sendError($exception->getMessage());
        } catch (GenerateException $exception) {
            Log::channel('generation')->error($exception->getMessage());
            return $this->sendError($exception->getMessage());
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());
            return $this->sendError($exception->getMessage());
        }

        return $this->sendResponse([
            'story' => $story,
        ]);
    }

    private function saveStory(CreateStoryTextExerciseDTO $exercise, string $text): void
    {
        $tu = TelegramUser::updateOrCreate(
            ['telegram_id' => 123],
            [
                'first_name' => 'Васян',
                'last_name'     => 'Петрович',
                'username'      => '@vasyAn',
                'language_code' => 8,
                'is_premium'    => false,
            ]
        );

        $tu->stories()->create([
            'source' => StorySourceEnum::TELEGRAM,
            'type' => $exercise->getType(),
            'status' => StoryStatusEnum::PENDING,
            'request_text' => $exercise->getRequestText(),
            'result_text' => $text,
            'child_data' => $exercise->getChild()->toArray(),
        ]);
    }
}
