<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason_code' => ['nullable', 'string', 'max:50'],
            'reason_text' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

