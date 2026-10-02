<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentDecisionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    public function rules(): array { return ['status' => ['required', Rule::in([Payment::VERIFIED, Payment::REJECTED])], 'admin_note' => ['nullable', 'string', 'max:2000', 'required_if:status,'.Payment::REJECTED]]; }
}
