<?php

namespace App\Http\Controllers\Admin;

use App\Exports\IntellectualPropertiesExport;
use App\Imports\IntellectualPropertiesImport;
use App\Http\Controllers\Controller;
use App\Models\IntellectualProperty;
use App\Services\IntellectualPropertyReport;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Inertia\Inertia;
use Inertia\Response;

class IntellectualPropertyController extends Controller
{
    public function __construct(private readonly IntellectualPropertyReport $report)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', IntellectualProperty::class);

        $records = $this->report->query($request)
            ->latest('application_date')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/IntellectualProperties/Index', [
            'records' => $records,
            'filters' => [
                'period_start' => $request->input('period_start', ''),
                'period_end' => $request->input('period_end', ''),
                'institution' => $request->input('institution', ''),
                'type' => $request->input('type', ''),
                'group' => $request->input('group', 'month'),
            ],
            'summary' => $this->report->summary($request),
            'chart' => $this->report->chart($request),
            'options' => $this->report->filterOptions(),
        ]);
    }

    public function show(IntellectualProperty $intellectualProperty): Response
    {
        $this->authorize('view', $intellectualProperty);

        return Inertia::render('Admin/IntellectualProperties/Show', [
            'record' => $intellectualProperty->load('user.memberProfile'),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', IntellectualProperty::class);

        $records = $this->report->query($request)
            ->orderBy('application_date')
            ->get();

        return Excel::download(new IntellectualPropertiesExport($records), 'data-hki-'.now()->format('Ymd-His').'.xlsx');
    }

    public function importTemplate(): BinaryFileResponse
    {
        $this->authorize('viewAny', IntellectualProperty::class);

        return Excel::download(new \App\Exports\IntellectualPropertiesImportTemplateExport(), 'template-import-data-hki.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', IntellectualProperty::class);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:10240'],
        ], [
            'file.required' => 'Pilih file Excel yang akan diimpor.',
            'file.mimes' => 'File harus berformat XLSX atau CSV.',
            'file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $import = new IntellectualPropertiesImport();
        try {
            DB::transaction(fn () => Excel::import($import, $request->file('file')));
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withErrors(['import' => 'File gagal diproses. Pastikan memakai template import Data HKI yang disediakan.']);
        }

        $counts = $import->counts();
        return back()->with('success', "Import selesai. {$counts['created']} data ditambahkan dan {$counts['updated']} data diperbarui.");
    }
}
