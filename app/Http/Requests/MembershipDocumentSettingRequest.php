<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class MembershipDocumentSettingRequest extends FormRequest { public function authorize(): bool { return $this->user()?->role === 'admin'; } public function rules(): array { return ['title' => ['required','string','max:120'], 'number_prefix' => ['required','string','max:40'], 'background_color' => ['required','regex:/^#[0-9A-Fa-f]{6}$/'], 'accent_color' => ['required','regex:/^#[0-9A-Fa-f]{6}$/']]; } }
