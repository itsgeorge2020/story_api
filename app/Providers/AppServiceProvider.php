<?php

namespace App\Providers;

use App\Contracts\ImageGenerationServiceInterface;
use App\Contracts\TextPromptServiceInterface;
use App\Contracts\TextRequestServiceInterface;
use App\Events\StoryGenerationFailedEvent;
use App\Events\StoryReadyEvent;
use App\Services\GenerateImages\CloudflareWorkersImageGenerationService;
use App\Services\GenerateTexts\DeepSeekTextPromptService;
use App\Services\GenerateTexts\DeepSeekTextRequestService;
use App\Telegram\Listeners\SendFailureToTelegramListener;
use App\Telegram\Listeners\SendStoryToTelegramListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TextRequestServiceInterface::class, DeepSeekTextRequestService::class);
        $this->app->bind(TextPromptServiceInterface::class, DeepSeekTextPromptService::class);

        $this->app->singleton(ImageGenerationServiceInterface::class, CloudflareWorkersImageGenerationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            StoryReadyEvent::class,
            SendStoryToTelegramListener::class,
        );

        Event::listen(
            StoryGenerationFailedEvent::class,
            SendFailureToTelegramListener::class,
        );
    }
}
