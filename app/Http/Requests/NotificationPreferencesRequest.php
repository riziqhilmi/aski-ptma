<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'member';
    }

    public function rules(): array
    {
        return [
            'membership_updates' => ['required', 'boolean'],
            'invoice_updates' => ['required', 'boolean'],
            'payment_updates' => ['required', 'boolean'],
            'document_updates' => ['required', 'boolean'],
            'news_updates' => ['required', 'boolean'],
        ];
    }
}
