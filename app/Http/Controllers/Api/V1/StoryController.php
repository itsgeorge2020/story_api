<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\TextPromptServiceInterface;
use App\Contracts\TextRequestServiceInterface;
use App\DTO\CreateStoryTextExerciseDTO;
use App\Exceptions\DeepSeekException;
use App\Exceptions\GenerateException;
use App\Http\Requests\Api\V1\StoryCreateRequest;
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
}
