<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupportTicketResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['answered', 'closed'])],
            'admin_response' => ['required_if:status,answered', 'nullable', 'string', 'max:5000'],
        ];
    }
}
