<?php

namespace App\DTO;

use App\Http\Requests\Api\V1\StoryCreateRequest;
use App\Enums\StoryTypeEnum;

class CreateStoryTextExerciseDTO
{
    public function __construct(
        private ChildDTO $child,
        private string $requestText,
        private StoryTypeEnum $type
    ) {}

    public static function createFromRequest(StoryCreateRequest $request): self
    {
        $childData = [
            'name' => $request->input('name'),
            'age' => $request->input('age'),
            'gender' => $request->input('gender')
        ];
        $child = ChildDTO::createFromRequest($childData);

        $type = StoryTypeEnum::getByCase($request->input('type'));

        return new self(
            child: $child,
            requestText: $request->input('text'),
            type: $type
        );
    }

    public function getChild(): ChildDTO
    {
        return $this->child;
    }

    public function getRequestText(): string
    {
        return $this->requestText;
    }

    public function getType(): StoryTypeEnum
    {
        return $this->type;
    }
}
