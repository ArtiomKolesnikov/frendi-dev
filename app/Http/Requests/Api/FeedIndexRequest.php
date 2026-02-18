<?php

namespace App\Http\Requests\Api;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $types = $this->input('types');
        if (is_string($types)) {
            $types = array_filter(array_map('trim', explode(',', $types)));
            $this->merge(['types' => $types]);
        }
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'types' => ['sometimes', 'array'],
            'types.*' => ['string', Rule::in(Post::TYPES)],
        ];
    }
}

