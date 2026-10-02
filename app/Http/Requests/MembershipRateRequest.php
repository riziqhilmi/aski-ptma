<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MembershipRateRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    public function rules(): array { return ['account_type' => ['required', Rule::in(['individual', 'institution'])], 'name' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'min:0'], 'duration_months' => ['required', 'integer', 'min:1', 'max:120'], 'active_from' => ['required', 'date'], 'active_until' => ['nullable', 'date', 'after_or_equal:active_from']]; }
}
