<?php

namespace App\Jobs;

use App\Actions\Story\SaveGeneratedStoryAction;
use App\Contracts\TextPromptServiceInterface;
use App\Contracts\TextRequestServiceInterface;
use App\Contracts\UserInterface;
use App\DTO\CreateStoryTextExerciseDTO;
use App\Enums\StorySourceEnum;
use App\Events\StoryGenerationFailedEvent;
use App\Exceptions\DeepSeekException;
use App\Exceptions\GenerateException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Queue('deepseek')]
#[Tries(3)]
#[Timeout(120)]
#[Backoff([10, 30, 60])]
class GenerateStoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly CreateStoryTextExerciseDTO $exercise,
        private readonly UserInterface $user,
        private readonly StorySourceEnum $source
    ) {}

    /**
     * @throws DeepSeekException
     */
    public function handle(
        TextPromptServiceInterface $textPromptService,
        TextRequestServiceInterface $textRequestService,
        SaveGeneratedStoryAction $saveAction,
    ): void {
        try {
            $prompt = $textPromptService->createPrompt($this->exercise);
            $storyData  = $textRequestService->send($prompt);
        } catch (GenerateException $exception) {
            // модель вернула мусор — ретраить бессмысленно
            $this->fail($exception);
            return;
        } catch (DeepSeekException $exception) {
            // сеть/API — пусть Laravel ретраит
            Log::channel('deepseekApi')->error($exception->getMessage());
            throw $exception;
        }

        $saveAction->handle(
            exercise: $this->exercise,
            storyData: $storyData,
            user: $this->user,
            source: $this->source
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('generation')->error('GenerateStoryJob failed: ' . $exception->getMessage());

        StoryGenerationFailedEvent::dispatch($this->user, $this->source);
    }
}
