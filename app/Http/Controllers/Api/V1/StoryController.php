<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\CreateStoryTextExerciseDTO;
use App\Enums\StorySourceEnum;
use App\Http\Requests\Api\V1\StoryCreateRequest;
use App\Jobs\GenerateStoryJob;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;

class StoryController extends BaseController
{
    public function store(StoryCreateRequest $request): JsonResponse
    {
        $user = TelegramUser::query()->find(1)->first();
        $exercise = CreateStoryTextExerciseDTO::createFromRequest($request);

        GenerateStoryJob::dispatch($exercise, $user, StorySourceEnum::TELEGRAM);

        return $this->sendResponse();
    }
}
