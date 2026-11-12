<?php
namespace App\Services;
use App\Models\IssuedMembershipDocument;
use App\Models\Membership;
use App\Models\MembershipDocumentSetting;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class MembershipDocumentService
{
    public function issue(Membership $membership, string $type): IssuedMembershipDocument
    {
        if (blank($membership->member_number)) {
            $year = now()->year;
            $membership->update(['member_number' => $this->nextMemberNumber($year)]);
            $membership->refresh();
        }
        $existing = IssuedMembershipDocument::where(['membership_id' => $membership->id, 'type' => $type])->first();
        if ($existing) return $existing;
        $membership->loadMissing('user.memberProfile');
        $setting = MembershipDocumentSetting::firstOrCreate(['type' => $type], ['title' => $type === IssuedMembershipDocument::CERTIFICATE ? 'SERTIFIKAT KEANGGOTAAN' : 'KARTU ANGGOTA', 'number_prefix' => $type === IssuedMembershipDocument::CERTIFICATE ? 'CERT/ASKI' : 'ASKI']);
        $profile = $membership->user->memberProfile;
        $year = now()->year;
        $prefix = $setting->number_prefix;
        $number = $prefix.'/'.$year.'/'.str_pad((string) (IssuedMembershipDocument::where('type', $type)->whereYear('issued_at', $year)->lockForUpdate()->count() + 1), 4, '0', STR_PAD_LEFT);
        $snapshot = ['name' => $membership->user->name, 'account_type' => $profile?->account_type, 'institution_name' => $profile?->institution_name, 'member_number' => $membership->member_number, 'period_start' => $membership->starts_at->toDateString(), 'period_end' => $membership->ends_at->toDateString(), 'issued_at' => now()->toDateString(), 'number' => $number, 'title' => $setting->title, 'background_color' => $setting->background_color, 'accent_color' => $setting->accent_color];
        $path = 'membership-documents/'.$membership->user_id.'/'.Str::slug($number).'.pdf';
        $pdfContent = $type === IssuedMembershipDocument::CARD
            ? $this->renderCardPdf($snapshot)
            : $this->renderCertificatePdf($snapshot);
        Storage::disk('local')->put($path, $pdfContent);
        return IssuedMembershipDocument::create(['membership_id' => $membership->id, 'user_id' => $membership->user_id, 'type' => $type, 'number' => $number, 'file_path' => $path, 'snapshot' => $snapshot, 'issued_at' => now()]);
    }

    public function renderCardPdf(array $data): string
    {
        $data['background_color'] = $this->safeColor($data['background_color'] ?? null, '#0b6254');
        $data['accent_color'] = $this->safeColor($data['accent_color'] ?? null, '#d4a72c');
        $logoPath = public_path('images/logo-aski.png');
        $logo = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : '';

        $pdf = new Dompdf();
        $pdf->setPaper([0, 0, 153.07, 242.65], 'landscape');
        $pdf->loadHtml(view('pdf.membership-card', ['data' => $data, 'logo' => $logo])->render());
        $pdf->render();

        return $pdf->output();
    }

    private function renderCertificatePdf(array $data): string
    {
        $pdf = new Dompdf();
        $pdf->setPaper('a4', 'landscape');
        $pdf->loadHtml(view('pdf.membership-certificate', ['data' => $data])->render());
        $pdf->render();

        return $pdf->output();
    }

    private function safeColor(?string $color, string $fallback): string
    {
        return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1
            ? $color
            : $fallback;
    }

    private function nextMemberNumber(int $year): string
    {
        $prefix = 'ASKI/'.$year.'/';
        $existingNumbers = Membership::query()
            ->where('member_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('member_number');

        $highestSequence = $existingNumbers
            ->map(function (string $number) use ($prefix): int {
                $suffix = substr($number, strlen($prefix));

                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max() ?? 0;

        $sequence = $highestSequence + 1;
        do {
            $candidate = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (Membership::query()->where('member_number', $candidate)->exists());

        return $candidate;
    }

    public function issueAll(Membership $membership): void { $this->issue($membership, IssuedMembershipDocument::CERTIFICATE); $this->issue($membership, IssuedMembershipDocument::CARD); }
}
