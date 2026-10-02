<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationMemberRequest;
use App\Models\OrganizationMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationMemberController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', OrganizationMember::class);

        return Inertia::render('Admin/OrganizationMembers/Index', [
            'members' => OrganizationMember::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', OrganizationMember::class);

        return Inertia::render('Admin/OrganizationMembers/Form', [
            'member' => null,
        ]);
    }

    public function store(OrganizationMemberRequest $request): RedirectResponse
    {
        $this->authorize('create', OrganizationMember::class);
        $data = $request->validated();
        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('organization-members', 'local')
            : null;

        OrganizationMember::create([
            'name' => $data['name'],
            'position' => $data['position'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'],
            'is_active' => $data['is_active'],
            'photo_path' => $photoPath,
            'photo_mime' => $request->file('photo')?->getMimeType(),
        ]);

        return to_route('admin.organization-members.index')->with('success', 'Struktur organisasi berhasil ditambahkan.');
    }

    public function edit(OrganizationMember $organizationMember): Response
    {
        $this->authorize('update', $organizationMember);

        return Inertia::render('Admin/OrganizationMembers/Form', [
            'member' => $organizationMember,
        ]);
    }

    public function update(OrganizationMemberRequest $request, OrganizationMember $organizationMember): RedirectResponse
    {
        $this->authorize('update', $organizationMember);
        $data = $request->validated();
        $payload = [
            'name' => $data['name'],
            'position' => $data['position'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'],
            'is_active' => $data['is_active'],
        ];

        if ($request->hasFile('photo')) {
            Storage::disk('local')->delete($organizationMember->photo_path);
            $payload['photo_path'] = $request->file('photo')->store('organization-members', 'local');
            $payload['photo_mime'] = $request->file('photo')->getMimeType();
        }

        $organizationMember->update($payload);

        return to_route('admin.organization-members.index')->with('success', 'Struktur organisasi berhasil diperbarui.');
    }

    public function destroy(OrganizationMember $organizationMember): RedirectResponse
    {
        $this->authorize('delete', $organizationMember);

        $organizationMember->delete();

        return to_route('admin.organization-members.index')->with('success', 'Struktur organisasi berhasil dihapus.');
    }
}
