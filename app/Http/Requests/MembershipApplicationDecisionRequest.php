<?php

namespace App\Http\Requests;

use App\Models\MembershipApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MembershipApplicationDecisionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([MembershipApplication::REVISION, MembershipApplication::APPROVED_WAITING_PAYMENT, MembershipApplication::REJECTED])],
            'admin_note' => ['nullable', 'string', 'max:2000', 'required_if:status,'.MembershipApplication::REVISION.', '.MembershipApplication::REJECTED],
        ];
    }
}
