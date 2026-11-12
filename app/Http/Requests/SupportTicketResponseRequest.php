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
        $allowedStatuses = $this->route('supportTicket')?->status === \App\Models\SupportTicket::STATUS_CLOSED
            ? [\App\Models\SupportTicket::STATUS_OPEN]
            : [\App\Models\SupportTicket::STATUS_ANSWERED, \App\Models\SupportTicket::STATUS_CLOSED];

        return [
            'status' => ['required', Rule::in($allowedStatuses)],
            'admin_response' => ['required_if:status,answered', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status tindak lanjut wajib dipilih.',
            'status.in' => 'Tiket tertutup harus dibuka kembali sebelum dapat dijawab.',
            'admin_response.required_if' => 'Jawaban Admin wajib diisi untuk menandai tiket terjawab.',
            'admin_response.max' => 'Jawaban Admin tidak boleh lebih dari 5000 karakter.',
        ];
    }
}
