<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();

        $accounts = User::query()
            ->where('role', 'member')
            ->with('memberProfile')
            ->when(in_array($type, ['individual', 'institution'], true), fn ($query) => $query->whereHas('memberProfile', fn ($profile) => $profile->where('account_type', $type)))
            ->when($type === 'unselected', fn ($query) => $query->whereDoesntHave('memberProfile'))
            ->when($status === 'belum_lengkap', function ($query): void {
                $query->where(function ($accounts): void {
                    $accounts->whereDoesntHave('memberProfile')
                        ->orWhereHas('memberProfile', function ($profile): void {
                            $profile->whereNull('account_type')
                                ->orWhereNull('phone')
                                ->orWhereNull('address')
                                ->orWhere(function ($institution): void {
                                    $institution->where('account_type', 'institution')
                                        ->where(function ($missing): void {
                                            $missing->whereNull('institution_name')->orWhereNull('responsible_name');
                                        });
                                });
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_type' => $user->memberProfile?->account_type,
                'completion_status' => $user->memberProfile?->completion_status ?? 'belum lengkap',
            ]);

        return Inertia::render('Admin/Accounts/Index', [
            'accounts' => $accounts,
            'filters' => ['type' => $type, 'status' => $status],
        ]);
    }

    public function show(User $user): Response
    {
        abort_unless($user->role === 'member', 404);
        $user->load('memberProfile');

        return Inertia::render('Admin/Accounts/Show', [
            'account' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->memberProfile,
                'completion_status' => $user->memberProfile?->completion_status ?? 'belum lengkap',
            ],
        ]);
    }
}
