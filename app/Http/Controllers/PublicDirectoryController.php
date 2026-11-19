<?php

namespace App\Http\Controllers;

use App\Models\IntellectualProperty;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PublicDirectoryController extends Controller
{
    public function members(Request $request): Response
    {
        $type =$request->string('type')->toString();
        $search =$request->string('search')->toString();

        $members = Membership::query()
            ->with(['user.memberProfile'])
            ->where('status', 'active')
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->whereHas('user.memberProfile', function (Builder $query) use ($type, $search) {$query
                    ->when(in_array($type, ['individual', 'institution'], true), fn (Builder$profile) => $profile->where('account_type',$type))
                    ->when($search !== '', function (Builder$profile) use ($search) {$profile->where(function (Builder $nested) use ($search) {
                            $nested->where('institution_name', 'like', '\%'.$search.'%')
                                ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', '\%'.$search.'%'));
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
        $type =$request->string('type')->toString();
        $search =$request->string('search')->toString();
        $year =$request->string('year')->toString();
        $sort =$request->string('sort', 'latest')->toString();

        $records = IntellectualProperty::query()
            ->with('user.memberProfile')
            ->when($type !== '', fn (Builder$query) => $query->where('ip_type',$type))
            ->when($year !== '', function (Builder$query) use ($year) {$query->where(function (Builder $nested) use ($year) {
                    $nested->whereYear('application_date',$year)
                        ->orWhere(function (Builder $fallback) use ($year) {$fallback->whereNull('application_date')
                                ->whereYear('created_at', $year);
                        });
                });
            })
            ->when($search !== '', function (Builder$query) use ($search) {$query->where(function (Builder $nested) use ($search) {
                    $nested->where('title', 'like', '\%'.$search.'%')
                        ->orWhere('creator', 'like', '%'.$search.'%')
                        ->orWhere('rights_holder', 'like', '%'.$search.'%');
                });
            })
            ->when($sort === 'oldest', fn (Builder $query) =>$query->oldest('application_date'))
            ->when($sort === 'title', fn (Builder $query) =>$query->orderBy('title'))
            ->when(! in_array($sort, ['oldest', 'title'], true), fn (Builder $query) =>$query->latest('application_date'))
            ->paginate(12)
            ->withQueryString();

        $byType = IntellectualProperty::query()
            ->selectRaw('ip_type, COUNT(*) as total')
            ->whereNotNull('ip_type')
            ->where('ip_type', '!=', '')
            ->groupBy('ip_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => trim((string) $row->ip_type),
                'total' => (int) $row->total,
            ])
            ->values();

        $dateRows = IntellectualProperty::query()
            ->select(['application_date', 'created_at'])
            ->get();

        $allYears =$dateRows
            ->map(function ($item) {$date = $item->application_date ?: $item->created_at;

                return $date ? Carbon::parse($date)->format('Y') : null;
            })
            ->filter()
            ->countBy()
            ->sortKeys();

        $byYear =$allYears
            ->take(-6)
            ->map(fn ($total,$yr) => [
                'year' => (string) $yr,
                'total' => (int) $total,
            ])
            ->values();

        $topHolders = IntellectualProperty::query()
            ->selectRaw('rights_holder, COUNT(*) as total')
            ->whereNotNull('rights_holder')
            ->where('rights_holder', '!=', '')
            ->groupBy('rights_holder')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'name' => trim((string) $row->rights_holder),
                'total' => (int) $row->total,
            ])
            ->values();

        $latestYearEntry =$byYear->last();

        return Inertia::render('Public/IntellectualProperties/Index', [
            'records' => $records,
            'filters' => [
                'type' => $type,
                'search' => $search,
                'year' => $year,
                'sort' => $sort,
            ],
            'summary' => [
                'total' => IntellectualProperty::count(),
                'types' => IntellectualProperty::distinct('ip_type')->count('ip_type'),
                'members' => IntellectualProperty::distinct('user_id')->count('user_id'),
                'latest_year' => $latestYearEntry['year'] ?? date('Y'),
                'latest_year_total' => $latestYearEntry['total'] ?? 0,
            ],
            'types' => IntellectualProperty::distinct()->orderBy('ip_type')->pluck('ip_type'),
            'years' => $allYears->keys()->reverse()->values(),
            'analytics' => [
                'byType' => $byType,
                'byYear' => $byYear,
                'topHolders' => $topHolders,
            ],
        ]);
    }

    private function filteredIntellectualPropertyQuery(Request $request): Builder
    {
        $type = $request->string('type')->toString();
        $search = $request->string('search')->toString();
        $year = $request->string('year')->toString();
        $sort = $request->string('sort', 'latest')->toString();

        return IntellectualProperty::query()
            ->with('user.memberProfile')
            ->when($type !== '', fn (Builder $query) => $query->where('ip_type', $type))
            ->when($year !== '', function (Builder $query) use ($year) {
                $query->where(function (Builder $nested) use ($year) {
                    $nested->whereYear('application_date', $year)
                        ->orWhere(function (Builder $fallback) use ($year) {
                            $fallback->whereNull('application_date')
                                ->whereYear('created_at', $year);
                        });
                });
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested->where('title', 'like', '%'.$search.'%')
                        ->orWhere('creator', 'like', '%'.$search.'%')
                        ->orWhere('rights_holder', 'like', '%'.$search.'%');
                });
            })
            ->when($sort === 'oldest', fn (Builder $query) => $query->oldest('application_date'))
            ->when($sort === 'title', fn (Builder $query) => $query->orderBy('title'))
            ->when(! in_array($sort, ['oldest', 'title'], true), fn (Builder $query) => $query->latest('application_date'));
    }

    public function exportIntellectualProperties(Request $request): StreamedResponse
    {
        $records = $this->filteredIntellectualPropertyQuery($request)->get();
        $filename = 'rekap-hki-aski-ptma-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar kolom otomatis rapi saat dibuka di Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Judul Karya / Invensi',
                'Jenis HKI',
                'Pencipta',
                'Pemegang Hak',
                'Tanggal Permohonan',
                'Institusi Anggota',
                'Deskripsi',
            ]);

            foreach ($records as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    $item->title,
                    $item->ip_type,
                    $item->creator ?? '-',
                    $item->rights_holder ?? '-',
                    optional($item->application_date)->format('Y-m-d') ?? (string) $item->application_date,
                    $item->user?->memberProfile?->institution_name ?? 'Anggota ASKI-PTMA',
                    $item->description ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}