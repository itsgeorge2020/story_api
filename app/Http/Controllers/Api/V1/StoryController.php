<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\CreateStoryTextExerciseDTO;
use App\Http\Requests\Api\V1\StoryCreateRequest;
use App\Jobs\GenerateStoryJob;
use Illuminate\Http\JsonResponse;

class StoryController extends BaseController
{
    public function store(StoryCreateRequest $request): JsonResponse
    {
        $exercise = CreateStoryTextExerciseDTO::createFromRequest($request);

        GenerateStoryJob::dispatch($exercise)
            ->onQueue('deepseek');

        return $this->sendResponse();
    }
}
