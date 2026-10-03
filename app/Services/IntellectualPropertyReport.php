<?php

namespace App\Services;

use App\Models\IntellectualProperty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntellectualPropertyReport
{
    public function query(Request $request): Builder
    {
        return IntellectualProperty::query()
            ->with(['user.memberProfile'])
            ->when($request->filled('period_start'), fn (Builder $query) => $query->whereDate('application_date', '>=', $request->date('period_start')))
            ->when($request->filled('period_end'), fn (Builder $query) => $query->whereDate('application_date', '<=', $request->date('period_end')))
            ->when($request->filled('type'), fn (Builder $query) => $query->where('ip_type', $request->string('type')->toString()))
            ->when($request->filled('institution'), function (Builder $query) use ($request) {
                $query->whereHas('user.memberProfile', fn (Builder $profile) => $profile->where('institution_name', $request->string('institution')->toString()));
            });
    }

    public function chart(Request $request): array
    {
        $group = $request->input('group', 'month') === 'year' ? 'year' : 'month';
        $expression = match (DB::connection()->getDriverName()) {
            'sqlite' => $group === 'year' ? "strftime('%Y', application_date)" : "strftime('%Y-%m', application_date)",
            default => $group === 'year' ? "DATE_FORMAT(application_date, '%Y')" : "DATE_FORMAT(application_date, '%Y-%m')",
        };

        return $this->query($request)
            ->select(DB::raw($expression.' as label'), DB::raw('COUNT(*) as total'))
            ->groupBy('label')
            ->orderBy('label')
            ->get()
            ->map(fn ($item) => ['label' => $item->label, 'total' => (int) $item->total])
            ->values()
            ->all();
    }

    public function summary(Request $request): array
    {
        $filtered = $this->query($request);

        return [
            'total' => (int) (clone $filtered)->count(),
            'types' => (int) (clone $filtered)->distinct('ip_type')->count('ip_type'),
            'institutions' => (int) (clone $filtered)
                ->whereHas('user.memberProfile', fn (Builder $profile) => $profile->whereNotNull('institution_name'))
                ->distinct('user_id')
                ->count('user_id'),
        ];
    }

    public function filterOptions(): array
    {
        return [
            'types' => IntellectualProperty::query()->distinct()->orderBy('ip_type')->pluck('ip_type'),
            'institutions' => IntellectualProperty::query()
                ->join('member_profiles', 'member_profiles.user_id', '=', 'intellectual_properties.user_id')
                ->whereNotNull('member_profiles.institution_name')
                ->distinct()
                ->orderBy('member_profiles.institution_name')
                ->pluck('member_profiles.institution_name'),
        ];
    }
}
