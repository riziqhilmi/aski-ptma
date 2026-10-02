<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntellectualPropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'member';
    }

    public function rules(): array
    {
        return [
            'application_number' => ['required', 'string', 'max:255'],
            'application_date' => ['required', 'date'],
            'creator' => ['required', 'string', 'max:255'],
            'rights_holder' => ['required', 'string', 'max:255'],
            'ip_type' => ['required', 'string', 'max:100'],
            'recordation_number' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:500'],
            'first_published_at' => ['required', 'date'],
            'protection_period' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'application_number' => 'nomor permohonan',
            'application_date' => 'tanggal permohonan',
            'creator' => 'pencipta',
            'rights_holder' => 'pemegang hak',
            'ip_type' => 'jenis HKI',
            'recordation_number' => 'nomor pencatatan',
            'title' => 'judul ciptaan',
            'first_published_at' => 'tanggal pertama kali diumumkan',
            'protection_period' => 'jangka waktu pelindungan',
        ];
    }
}
