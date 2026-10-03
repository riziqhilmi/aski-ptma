<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipDocumentSettingRequest;
use App\Models\IssuedMembershipDocument;
use App\Models\MembershipDocumentSetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
class MembershipDocumentSettingController extends Controller
{
    public function index() { return Inertia::render('Admin/MembershipDocuments/Settings', ['settings' => collect([IssuedMembershipDocument::CERTIFICATE, IssuedMembershipDocument::CARD])->map(fn ($type) => MembershipDocumentSetting::firstOrCreate(['type' => $type], ['title' => $type === 'certificate' ? 'SERTIFIKAT KEANGGOTAAN' : 'KARTU ANGGOTA', 'number_prefix' => $type === 'certificate' ? 'CERT/ASKI' : 'ASKI'])->fresh())]); }
    public function update(MembershipDocumentSettingRequest $request, MembershipDocumentSetting $setting): RedirectResponse { $setting->update($request->validated()); return back()->with('success', 'Format dokumen berhasil disimpan.'); }
}
