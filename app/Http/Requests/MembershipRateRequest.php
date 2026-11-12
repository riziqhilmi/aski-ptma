<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MembershipRateRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    public function rules(): array { return ['account_type' => ['required', Rule::in(['individual', 'institution'])], 'name' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'min:0'], 'duration_months' => ['required', 'integer', 'min:1', 'max:120'], 'active_from' => ['required', 'date'], 'active_until' => ['nullable', 'date', 'after_or_equal:active_from']]; }

    public function attributes(): array
    {
        return [
            'account_type' => 'tipe akun',
            'name' => 'nama tarif',
            'amount' => 'nominal tarif',
            'duration_months' => 'durasi keanggotaan',
            'active_from' => 'tanggal mulai berlaku',
            'active_until' => 'tanggal akhir berlaku',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'account_type.in' => 'Tipe akun harus Individu atau Institusi.',
            'amount.numeric' => 'Nominal tarif harus berupa angka.',
            'amount.min' => 'Nominal tarif tidak boleh kurang dari Rp0.',
            'duration_months.integer' => 'Durasi keanggotaan harus berupa bilangan bulan.',
            'duration_months.min' => 'Durasi keanggotaan minimal 1 bulan.',
            'duration_months.max' => 'Durasi keanggotaan maksimal 120 bulan.',
            'active_until.after_or_equal' => 'Tanggal akhir berlaku harus sama dengan atau setelah tanggal mulai berlaku.',
        ];
    }
}
