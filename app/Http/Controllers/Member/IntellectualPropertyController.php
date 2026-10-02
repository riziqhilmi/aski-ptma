<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntellectualPropertyRequest;
use App\Models\IntellectualProperty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IntellectualPropertyController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', IntellectualProperty::class);

        $records = $request->user()->intellectualProperties()
            ->latest('application_date')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Member/IntellectualProperties/Index', [
            'records' => $records,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', IntellectualProperty::class);

        return Inertia::render('Member/IntellectualProperties/Form', [
            'record' => null,
        ]);
    }

    public function store(IntellectualPropertyRequest $request): RedirectResponse
    {
        $this->authorize('create', IntellectualProperty::class);

        $request->user()->intellectualProperties()->create($request->validated());

        return redirect()->route('member.intellectual-properties.index')
            ->with('success', 'Data HKI berhasil ditambahkan.');
    }

    public function show(IntellectualProperty $intellectualProperty): Response
    {
        $this->authorize('view', $intellectualProperty);

        return Inertia::render('Member/IntellectualProperties/Show', [
            'record' => $intellectualProperty,
        ]);
    }

    public function edit(IntellectualProperty $intellectualProperty): Response
    {
        $this->authorize('update', $intellectualProperty);

        return Inertia::render('Member/IntellectualProperties/Form', [
            'record' => $intellectualProperty,
        ]);
    }

    public function update(IntellectualPropertyRequest $request, IntellectualProperty $intellectualProperty): RedirectResponse
    {
        $this->authorize('update', $intellectualProperty);

        $intellectualProperty->update($request->validated());

        return redirect()->route('member.intellectual-properties.show', $intellectualProperty)
            ->with('success', 'Data HKI berhasil diperbarui.');
    }

    public function destroy(IntellectualProperty $intellectualProperty): RedirectResponse
    {
        $this->authorize('delete', $intellectualProperty);

        $intellectualProperty->delete();

        return redirect()->route('member.intellectual-properties.index')
            ->with('success', 'Data HKI berhasil dihapus.');
    }
}
