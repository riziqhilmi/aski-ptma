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
            $membership->update(['member_number' => 'ASKI/'.$year.'/'.str_pad((string) (Membership::whereYear('created_at', $year)->lockForUpdate()->count() + 1), 4, '0', STR_PAD_LEFT)]);
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
        $view = $type === IssuedMembershipDocument::CERTIFICATE ? 'pdf.membership-certificate' : 'pdf.membership-card';
        $pdf = new Dompdf();
        $pdf->setPaper($type === IssuedMembershipDocument::CERTIFICATE ? 'a4' : [0, 0, 242.65, 153.07], 'landscape');
        $pdf->loadHtml(view($view, ['data' => $snapshot])->render());
        $pdf->render();
        $path = 'membership-documents/'.$membership->user_id.'/'.Str::slug($number).'.pdf';
        Storage::disk('local')->put($path, $pdf->output());
        return IssuedMembershipDocument::create(['membership_id' => $membership->id, 'user_id' => $membership->user_id, 'type' => $type, 'number' => $number, 'file_path' => $path, 'snapshot' => $snapshot, 'issued_at' => now()]);
    }
    public function issueAll(Membership $membership): void { $this->issue($membership, IssuedMembershipDocument::CERTIFICATE); $this->issue($membership, IssuedMembershipDocument::CARD); }
}
