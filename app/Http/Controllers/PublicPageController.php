<?php

namespace App\Http\Controllers;

use App\Models\SiteContent;
use App\Models\OrganizationMember;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function profile(): Response
    {
        return Inertia::render('Public/Profile', [
            'profile' => $this->content(SiteContent::KEY_PROFILE),
            'visionMission' => $this->content(SiteContent::KEY_VISION_MISSION),
            'organizationMembers' => OrganizationMember::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Public/Contact', [
            'contact' => $this->content(SiteContent::KEY_CONTACT),
        ]);
    }

    private function content(string $key): array
    {
        $content = SiteContent::where('key', $key)->first();

        return [
            'title' => $content?->title ?? '',
            'body' => $content?->body ?? '',
            'metadata' => $content?->metadata ?? [],
        ];
    }
}
