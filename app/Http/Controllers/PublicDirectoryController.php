<?php

namespace App\Http\Controllers;

use App\Models\IntellectualProperty;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicDirectoryController extends Controller
{
    public function members(Request $request): Response
    {
        $type = $request->string('type')->toString();
        $search = $request->string('search')->toString();

        $members = Membership::query()
            ->with(['user.memberProfile'])
            ->where('status', 'active')
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->whereHas('user.memberProfile', function (Builder $query) use ($type, $search) {
                $query
                    ->when(in_array($type, ['individual', 'institution'], true), fn (Builder $profile) => $profile->where('account_type', $type))
                    ->when($search !== '', function (Builder $profile) use ($search) {
                        $profile->where(function (Builder $nested) use ($search) {
                            $nested->where('institution_name', 'like', '%'.$search.'%')
                                ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', '%'.$search.'%'));
                        });
                    });
            })
            ->latest('starts_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/Members/Index', [
            'members' => $members,
            'filters' => [
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    public function intellectualProperties(Request $request): Response
    {
        $type = $request->string('type')->toString();
        $search = $request->string('search')->toString();

        $records = IntellectualProperty::query()
            ->with('user.memberProfile')
            ->when($type !== '', fn (Builder $query) => $query->where('ip_type', $type))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('title', 'like', '%'.$search.'%')
                        ->orWhere('creator', 'like', '%'.$search.'%')
                        ->orWhere('rights_holder', 'like', '%'.$search.'%');
                });
            })
            ->latest('application_date')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Public/IntellectualProperties/Index', [
            'records' => $records,
            'filters' => [
                'type' => $type,
                'search' => $search,
            ],
            'summary' => [
                'total' => IntellectualProperty::count(),
                'types' => IntellectualProperty::distinct('ip_type')->count('ip_type'),
                'members' => IntellectualProperty::distinct('user_id')->count('user_id'),
            ],
            'types' => IntellectualProperty::distinct()->orderBy('ip_type')->pluck('ip_type'),
        ]);
    }
}
