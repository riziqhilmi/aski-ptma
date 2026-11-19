<?php

namespace App\Imports;

use App\Models\IntellectualProperty;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MemberIntellectualPropertiesImport implements ToCollection, WithHeadingRow
{
    private int $created = 0;
    private int $updated = 0;

    public function __construct(private readonly int $memberId)
    {
    }

    public function collection(Collection $rows): void
    {
        $workTypes = [
            'Hak Cipta', 'Paten', 'Paten Sederhana', 'Merek', 'Desain Industri',
            'Indikasi Geografis', 'Rahasia Dagang', 'Desain Tata Letak Sirkuit Terpadu (DTLST)',
            'Perlindungan Varietas Tanaman (PVT)',
        ];
        $attributes = [
            'application_number' => 'nomor permohonan',
            'application_date' => 'tanggal permohonan',
            'creator' => 'pencipta',
            'rights_holder' => 'pemegang hak cipta',
            'ip_type' => 'jenis HKI',
            'recordation_number' => 'nomor pencatatan',
            'title' => 'judul ciptaan',
            'first_published_at' => 'tanggal pertama diumumkan',
            'protection_period' => 'jangka waktu pelindungan',
            'work_type' => 'jenis ciptaan',
        ];
        $rules = [
            'application_number' => ['required', 'string', 'max:255'],
            'application_date' => ['required', 'date'],
            'creator' => ['required', 'string', 'max:255'],
            'rights_holder' => ['required', 'string', 'max:255'],
            'ip_type' => ['required', 'in:Hak Cipta'],
            'recordation_number' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:500'],
            'first_published_at' => ['required', 'date'],
            'protection_period' => ['required', 'string', 'max:255'],
            'work_type' => ['required', 'in:'.implode(',', $workTypes)],
        ];

        $errors = [];
        $pending = [];
        $seenNumbers = [];

        foreach ($rows as $index => $row) {
            $data = $row->toArray();
            if (collect($data)->filter(fn ($value) => filled($value))->isEmpty()) {
                continue;
            }

            $line = $index + 2;
            $validator = Validator::make($data, $rules, [
                'required' => ':attribute wajib diisi.',
                'date' => ':attribute harus berupa tanggal yang valid.',
                'in' => ':attribute tidak sesuai pilihan yang tersedia untuk akun member.',
                'max' => ':attribute maksimal :max karakter.',
            ], $attributes);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$line}: {$message}";
                }
                continue;
            }

            $number = trim($data['application_number']);
            if (isset($seenNumbers[$number])) {
                $errors[] = "Baris {$line}: nomor permohonan {$number} muncul lebih dari sekali dalam file.";
                continue;
            }
            $seenNumbers[$number] = true;

            $pending[] = [
                'user_id' => $this->memberId,
                'application_number' => $number,
                'application_date' => $data['application_date'],
                'creator' => $data['creator'],
                'rights_holder' => $data['rights_holder'],
                'ip_type' => 'Hak Cipta',
                'recordation_number' => filled($data['recordation_number'] ?? null) ? $data['recordation_number'] : null,
                'title' => $data['title'],
                'first_published_at' => $data['first_published_at'],
                'protection_period' => $data['protection_period'],
                'type_details' => ['work_type' => $data['work_type']],
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => $errors]);
        }

        if ($pending === []) {
            throw ValidationException::withMessages(['file' => 'File tidak berisi baris data HKI yang dapat diimpor.']);
        }

        foreach ($pending as $values) {
            $record = IntellectualProperty::withTrashed()->firstOrNew([
                'user_id' => $this->memberId,
                'application_number' => $values['application_number'],
            ]);
            $wasExisting = $record->exists;
            $record->fill($values);
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
