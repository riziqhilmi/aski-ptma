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
            'admin_note' => ['nullable', 'string', 'max:2000', 'required_if:status,'.MembershipApplication::REVISION, 'required_if:status,'.MembershipApplication::REJECTED],
            'requested_fields' => ['exclude_unless:status,'.MembershipApplication::REVISION, 'required', 'array', 'min:1'],
            'requested_fields.*' => ['required', 'string', Rule::in(['name', 'email', 'account_type', 'phone', 'address', 'institution_name', 'responsible_name', 'responsible_position'])],
        ];
    }
}
