<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:'.implode(',', Post::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'contest_status' => ['nullable', 'in:new,past'],
            'author_display_name' => ['nullable', 'string', 'max:120'],
            'author_contact' => ['nullable', 'string', 'max:255'],
            'media' => ['nullable', 'array', 'max:10'],
            'media.*' => ['file', 'image', 'max:5120'],
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['integer', 'exists:post_media,id'],
        ];
    }
}
