<?php

namespace App\DTO;

readonly class TelegramUserDTO
{
    public function __construct(
        private int $userId,
        private int $chatId,
        private ?string $languageCode,
        private ?string $userName = null,
        private bool $isPremium = false,
        private ?string $firstName = null,
        private ?string $lastName = null
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }
}
