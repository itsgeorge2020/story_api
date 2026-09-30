<?php

namespace App\Actions\Story;

use App\Enums\GeneratedImageTypeEnum;
use App\Events\ImageGeneratedEvent;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;

class SaveGeneratedImageAction
{
    public function handle(Story $story, $stream): void
    {
        $filename = $this->buildFilename($story->id);

        Storage::disk('generated_images')->writeStream($filename, $stream);

        $image = $story->generatedImages()->create([
            'type'     => GeneratedImageTypeEnum::COVER,
            'disk'     => 'generated_images',
            'filename' => $filename,
        ]);

        ImageGeneratedEvent::dispatch($image);
    }

    private function buildFilename(int $storyId): string
    {
        return $storyId . '_' . uniqid() . '.jpg';
    }
}
