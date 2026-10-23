<?php

namespace App\DTO;

readonly class StoryChildDTO
{
    public function __construct(
        private ?string $name = null,
        private ?int $age = null,
        private ?string $gender = null
    ) {}

    public static function createFromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            age: $data['age'] ?? null,
            gender: $data['gender'] ?? null
        );
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'age' => $this->getAge(),
            'gender' => $this->getGender()
        ];
    }
}
