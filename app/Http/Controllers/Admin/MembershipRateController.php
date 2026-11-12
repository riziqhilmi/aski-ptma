<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipRateRequest;
use App\Models\MembershipRate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MembershipRateController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('Admin/MembershipRates/Index', [
            'rates' => MembershipRate::query()
                ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->latest('active_from')
                ->paginate(10)
                ->withQueryString(),
            'filters' => ['q' => $search],
        ]);
    }
    public function create(): Response { return Inertia::render('Admin/MembershipRates/Form', ['rate' => null]); }
    public function store(MembershipRateRequest $request): RedirectResponse { MembershipRate::create($request->validated()); return to_route('admin.rates.index')->with('success', 'Tarif berhasil ditambahkan.'); }
    public function edit(MembershipRate $rate): Response { return Inertia::render('Admin/MembershipRates/Form', ['rate' => $rate]); }
    public function update(MembershipRateRequest $request, MembershipRate $rate): RedirectResponse { $rate->update($request->validated()); return to_route('admin.rates.index')->with('success', 'Tarif berhasil diperbarui.'); }
    public function destroy(MembershipRate $rate): RedirectResponse { $rate->delete(); return to_route('admin.rates.index')->with('success', 'Tarif berhasil diarsipkan.'); }
}
