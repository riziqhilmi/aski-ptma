<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $fileRules = $this->isMethod('post')
            ? ['required', 'file', 'mimes:pdf', 'max:5120']
            : ['nullable', 'file', 'mimes:pdf', 'max:5120'];

        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(Document::CATEGORIES)],
            'status' => ['required', Rule::in(Document::STATUSES)],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => $fileRules,
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul dokumen',
            'category' => 'kategori',
            'status' => 'status',
            'description' => 'deskripsi',
            'file' => 'file PDF',
        ];
    }
}
