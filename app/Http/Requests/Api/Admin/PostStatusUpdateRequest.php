<?php

namespace App\Http\Requests\Api\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                Post::STATUS_PENDING,
                Post::STATUS_APPROVED,
                Post::STATUS_REJECTED,
            ])],
        ];
    }
}

