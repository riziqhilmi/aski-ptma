<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberProfileUpdateRequest;
use App\Models\MemberProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MemberProfileController extends Controller
{
    public function edit(): Response
    {
        $user = request()->user();
        $profile = $user->memberProfile ?? new MemberProfile();

        return Inertia::render('Member/Profile/Edit', [
            'user' => $user->only('name', 'email'),
            'profile' => $profile->only('account_type', 'phone', 'address', 'institution_name', 'responsible_name', 'responsible_position', 'completion_status'),
        ]);
    }

    public function update(MemberProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($request, $user): void {
            $user->update($request->safe()->only('name', 'email'));
            $profile = $user->memberProfile ?? new MemberProfile(['user_id' => $user->id]);

            if ($profile->exists) {
                $this->authorize('update', $profile);
            }

            $profile->fill($request->safe()->only('account_type', 'phone', 'address', 'institution_name', 'responsible_name', 'responsible_position'));
            $profile->save();
        });

        return to_route('member.profile.edit')->with('success', 'Profil berhasil disimpan.');
    }
}
