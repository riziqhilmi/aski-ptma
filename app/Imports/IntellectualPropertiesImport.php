<?php

namespace App\Imports;

use App\Models\IntellectualProperty;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class IntellectualPropertiesImport implements ToCollection, WithHeadingRow
{
    private int $created = 0;
    private int $updated = 0;

    public function collection(Collection $rows): void
    {
        $workTypes = [
            'Hak Cipta', 'Paten', 'Paten Sederhana', 'Merek', 'Desain Industri',
            'Indikasi Geografis', 'Rahasia Dagang', 'Desain Tata Letak Sirkuit Terpadu (DTLST)',
            'Perlindungan Varietas Tanaman (PVT)',
        ];
        $errors = [];
        $pending = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = $row->toArray();
            $validator = Validator::make($data, [
                'member_email' => ['required', 'email', 'exists:users,email'],
                'application_number' => ['required', 'string', 'max:255'],
                'application_date' => ['required', 'date'],
                'creator' => ['required', 'string', 'max:255'],
                'rights_holder' => ['required', 'string', 'max:255'],
                'ip_type' => ['required', 'in:Hak Cipta,Paten,Merek,Desain Industri,Rahasia Dagang'],
                'recordation_number' => ['nullable', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:500'],
                'first_published_at' => ['nullable', 'date'],
                'protection_period' => ['required', 'string', 'max:255'],
                'work_type' => ['nullable', 'in:'.implode(',', $workTypes)],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$line}: {$message}";
                }
                continue;
            }

            if ($data['ip_type'] === 'Hak Cipta' && blank($data['first_published_at'])) {
                $errors[] = "Baris {$line}: tanggal pertama diumumkan wajib untuk Hak Cipta.";
                continue;
            }

            $member = User::where('email', $data['member_email'])->where('role', 'member')->first();
            if (! $member) {
                $errors[] = "Baris {$line}: email tidak terhubung ke akun Member.";
                continue;
            }

            $pending[] = [
                'user_id' => $member->id,
                'application_number' => $data['application_number'],
                'application_date' => $data['application_date'],
                'creator' => $data['creator'],
                'rights_holder' => $data['rights_holder'],
                'ip_type' => $data['ip_type'],
                'recordation_number' => $data['recordation_number'] ?? null,
                'title' => $data['title'],
                'first_published_at' => $data['first_published_at'] ?? null,
                'protection_period' => $data['protection_period'],
                'type_details' => filled($data['work_type'] ?? null) ? ['work_type' => $data['work_type']] : null,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        foreach ($pending as $attributes) {
            $record = IntellectualProperty::withTrashed()->firstOrNew([
                'user_id' => $attributes['user_id'],
                'application_number' => $attributes['application_number'],
            ]);
            $wasExisting = $record->exists;
            $record->fill($attributes);
            $record->deleted_at = null;
            $record->save();

            $wasExisting ? $this->updated++ : $this->created++;
        }
    }

    public function counts(): array
    {
        return ['created' => $this->created, 'updated' => $this->updated];
    }
}
