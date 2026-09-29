<?php

namespace App\DTO;

readonly class GeneratedTextDTO
{
    public function __construct(
        private string $text,
        private string $coverDescription
    ) {}

    public function getText(): string
    {
        return $this->text;
    }

    public function getCoverDescription(): string
    {
        return $this->coverDescription;
    }

    public function toArray(): array
    {
        return [
            'text' => $this->getText(),
            'cover_description' => $this->getCoverDescription()
        ];
    }
}
