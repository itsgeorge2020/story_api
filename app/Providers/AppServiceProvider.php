<?php

namespace App\Providers;

use App\Contracts\TextPromptServiceInterface;
use App\Contracts\TextRequestServiceInterface;
use App\Events\StoryGeneratedEvent;
use App\Events\StoryGenerationFailedEvent;
use App\Services\DeepSeekTextRequestService;
use App\Services\DeepSeekTextPromptService;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            StoryGeneratedEvent::class,
            SendStoryToTelegramListener::class,
        );

        Event::listen(
            StoryGenerationFailedEvent::class,
            SendFailureToTelegramListener::class,
        );
    }
}
