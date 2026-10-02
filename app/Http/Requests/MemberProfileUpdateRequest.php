<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'member';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'account_type' => ['required', Rule::in(['individual', 'institution'])],
            'phone' => ['required', 'string', 'max:25'],
            'address' => ['required', 'string', 'max:1000'],
            'institution_name' => ['nullable', 'required_if:account_type,institution', 'string', 'max:255'],
            'responsible_name' => ['nullable', 'required_if:account_type,institution', 'string', 'max:255'],
            'responsible_position' => ['nullable', 'string', 'max:255'],
        ];
    }
}
