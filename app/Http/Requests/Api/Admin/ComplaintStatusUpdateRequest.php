<?php

namespace App\Http\Requests\Api\Admin;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                Complaint::STATUS_PENDING,
                Complaint::STATUS_REVIEWED,
                Complaint::STATUS_REJECTED,
            ])],
        ];
    }
}

