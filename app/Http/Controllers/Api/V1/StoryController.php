<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\TextPromptServiceInterface;
use App\Contracts\TextRequestServiceInterface;
use App\DTO\CreateStoryTextExerciseDTO;
use App\Http\Requests\Api\V1\StoryCreateRequest;
use Illuminate\Http\JsonResponse;

class StoryController extends BaseController
{
    public function __construct(
        private readonly TextPromptServiceInterface  $textPromptService,
        private readonly TextRequestServiceInterface $textRequestService,
    ) {}

    public function store(StoryCreateRequest $request): JsonResponse
    {
        $exercise = CreateStoryTextExerciseDTO::createFromRequest($request);

        $prompt = $this->textPromptService->createPrompt($exercise);
        $story  = $this->textRequestService->send($prompt);

        return $this->sendResponse($story->toArray());
    }
}
