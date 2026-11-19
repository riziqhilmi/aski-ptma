<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntellectualPropertyRequest;
use App\Imports\MemberIntellectualPropertiesImport;
use App\Models\IntellectualProperty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

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
            'canCreate' => $request->user()->hasVerifiedMembershipPayment(),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->user()->hasVerifiedMembershipPayment()) {
            return redirect()->route('member.intellectual-properties.index')
                ->with('error', 'Input data HKI tersedia setelah pembayaran keanggotaan Anda terverifikasi.');
        }

        $this->authorize('create', IntellectualProperty::class);

        return Inertia::render('Member/IntellectualProperties/Form', [
            'record' => null,
        ]);
    }

    public function store(IntellectualPropertyRequest $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedMembershipPayment()) {
            return redirect()->route('member.intellectual-properties.index')
                ->with('error', 'Input data HKI tersedia setelah pembayaran keanggotaan Anda terverifikasi.');
        }

        $this->authorize('create', IntellectualProperty::class);

        $request->user()->intellectualProperties()->create($request->validated());

        return redirect()->route('member.intellectual-properties.index')
            ->with('success', 'Data HKI berhasil ditambahkan.');
    }

    public function import(Request $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedMembershipPayment()) {
            return redirect()->route('member.intellectual-properties.index')
                ->with('error', 'Import data HKI tersedia setelah pembayaran keanggotaan Anda terverifikasi.');
        }

        $this->authorize('create', IntellectualProperty::class);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:10240'],
        ], [
            'file.required' => 'Pilih file Excel atau CSV yang akan diimpor.',
            'file.mimes' => 'File harus berformat XLSX atau CSV.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $import = new MemberIntellectualPropertiesImport((int) $request->user()->getKey());

        try {
            DB::transaction(fn () => Excel::import($import, $request->file('file')));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['file' => 'File gagal diproses. Periksa format kolom dan isi data HKI.']);
        }

        $counts = $import->counts();

        return redirect()->route('member.intellectual-properties.index')
            ->with('success', "Import selesai. {$counts['created']} data ditambahkan dan {$counts['updated']} data diperbarui.");
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
