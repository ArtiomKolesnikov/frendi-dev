<?php

namespace App\Http\Requests\Api;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(Post::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
            'meta.contest_status' => ['nullable', Rule::in(['new', 'past'])],
            'author_display_name' => ['nullable', 'string', 'max:120'],
            'author_contact' => ['nullable', 'string', 'max:255'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'image', 'max:5120'],
        ];
    }
}

