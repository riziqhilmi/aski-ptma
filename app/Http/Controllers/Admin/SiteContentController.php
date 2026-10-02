<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteContentRequest;
use App\Models\SiteContent;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SiteContentController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaults();

        return Inertia::render('Admin/SiteContents/Index', [
            'contents' => SiteContent::query()->orderBy('id')->get(),
        ]);
    }

    public function edit(SiteContent $siteContent): Response
    {
        return Inertia::render('Admin/SiteContents/Form', [
            'content' => $siteContent,
        ]);
    }

    public function update(SiteContentRequest $request, SiteContent $siteContent): RedirectResponse
    {
        $siteContent->update([
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'metadata' => $request->validated('metadata') ?? [],
            'updated_by' => $request->user()->id,
        ]);

        return to_route('admin.site-contents.index')->with('success', 'Konten website berhasil diperbarui.');
    }

    private function ensureDefaults(): void
    {
        $defaults = [
            SiteContent::KEY_PROFILE => ['title' => 'Profil ASKI-PTMA', 'body' => 'ASKI-PTMA adalah asosiasi sentra kekayaan intelektual Perguruan Tinggi Muhammadiyah dan Aisyiyah.'],
            SiteContent::KEY_VISION_MISSION => ['title' => 'Visi dan Misi', 'body' => "Visi:\nMenjadi jejaring sentra KI PTMA yang unggul dan berdampak.\n\nMisi:\nMenguatkan layanan, kolaborasi, dan literasi kekayaan intelektual."],
            SiteContent::KEY_CONTACT => ['title' => 'Kontak ASKI-PTMA', 'body' => 'Hubungi pengelola ASKI-PTMA untuk informasi keanggotaan dan layanan HKI.', 'metadata' => []],
        ];

        foreach ($defaults as $key => $data) {
            SiteContent::firstOrCreate(['key' => $key], $data);
        }
    }
}
