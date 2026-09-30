<?php

namespace App\Jobs;

use App\Actions\Story\SaveGeneratedImageAction;
use App\Contracts\ImageGenerationServiceInterface;
use App\Enums\StoryStatusEnum;
use App\Events\StoryReadyEvent;
use App\Models\Story;
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

#[Queue('generate_image')]
#[Tries(3)]
#[Timeout(120)]
#[Backoff([10, 30, 60])]
class GenerateStoryImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Story $story
    ) {}

    public function handle(
        ImageGenerationServiceInterface $imageGen,
        SaveGeneratedImageAction $saveAction
    ): void {
        if ($this->story->cover()->exists()) {
            $this->markReady();
            return;
        }

        $prompt = trim((string) $this->story->cover_description);

        if ($prompt === '') {
            Log::channel('generationImage')
                ->warning("Story {$this->story->id}: cover_description пуст, отправляем без обложки");
            $this->markReady();
            return;
        }

        $stream = $imageGen->generateStream($prompt);

        try {
            $saveAction->handle($this->story, $stream);
        } finally {
            fclose($stream);
        }

        $this->markReady();
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('generationImage')->error(
            "[GenerateStoryImageJob failed for storyId: {$this->story->id}] " . $exception->getMessage()
        );

        $this->markReady();
    }

    private function markReady(): void
    {
        $story = $this->story->fresh();

        if ($story === null || $story->status !== StoryStatusEnum::PENDING) {
            return;
        }

        $story->status = StoryStatusEnum::READY;
        $story->save();

        StoryReadyEvent::dispatch($story);
    }
}
