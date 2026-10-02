<?php

namespace App\Http\Controllers;

use App\Models\OrganizationMember;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicOrganizationMemberPhotoController extends Controller
{
    public function __invoke(OrganizationMember $organizationMember): StreamedResponse
    {
        abort_unless($organizationMember->is_active && $organizationMember->photo_path, 404);
        abort_unless(Storage::disk('local')->exists($organizationMember->photo_path), 404);

        return Storage::disk('local')->response($organizationMember->photo_path, null, [
            'Content-Type' => $organizationMember->photo_mime ?: 'image/jpeg',
        ]);
    }
}
