<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SiteContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'metadata.email' => ['nullable', 'email', 'max:255'],
            'metadata.phone' => ['nullable', 'string', 'max:100'],
            'metadata.address' => ['nullable', 'string', 'max:1000'],
            'metadata.instagram' => ['nullable', 'string', 'max:255'],
            'metadata.website' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul',
            'body' => 'isi konten',
            'metadata.email' => 'email kontak',
            'metadata.phone' => 'nomor telepon',
            'metadata.address' => 'alamat',
            'metadata.instagram' => 'Instagram',
            'metadata.website' => 'website',
        ];
    }
}
