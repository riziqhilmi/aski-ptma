<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentUploadRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'member'; }
    public function rules(): array { return ['proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']]; }
}
