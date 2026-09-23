<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\StoryTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoryCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'age' => ['required', 'integer', 'min:0', 'max:12'],
            'gender' => ['required', 'in:boy,girl'],
            'type' => ['required', 'in:' . implode(',', StoryTypeEnum::values())],
            'text' => 'required|string|max:500',
        ];
    }
}
