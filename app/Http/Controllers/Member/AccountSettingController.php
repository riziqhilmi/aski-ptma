<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationPreferencesRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AccountSettingController extends Controller
{
    public function edit(): Response
    {
        $user = auth()->user()->load('memberProfile');

        return Inertia::render('Member/Settings/Edit', [
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile' => $user->memberProfile,
                'notification_preferences' => $this->preferences($user->notification_preferences),
            ],
        ]);
    }

    public function update(NotificationPreferencesRequest $request): RedirectResponse
    {
        $request->user()->update([
            'notification_preferences' => $request->validated(),
        ]);

        return redirect()->route('member.settings.edit')->with('success', 'Pengaturan notifikasi berhasil disimpan.');
    }

    private function preferences(?array $preferences): array
    {
        return array_merge([
            'membership_updates' => true,
            'invoice_updates' => true,
            'payment_updates' => true,
            'document_updates' => true,
            'news_updates' => false,
        ], $preferences ?? []);
    }
}
