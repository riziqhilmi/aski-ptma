<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $category = $request->string('category')->toString();
        $status = $request->string('status')->toString();

        return Inertia::render('Admin/Documents/Index', [
            'documents' => Document::with('uploader')
                ->when(in_array($category, Document::CATEGORIES, true), fn ($query) => $query->where('category', $category))
                ->when(in_array($status, Document::STATUSES, true), fn ($query) => $query->where('status', $status))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => [
                'category' => $category,
                'status' => $status,
            ],
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Document::class);

        return Inertia::render('Admin/Documents/Form', [
            'document' => null,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(DocumentRequest $request): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $file = $request->file('file');
        $data = $request->validated();
        $path = $file->store('documents', 'local');

        Document::create([
            'uploaded_by' => $request->user()->id,
            'title' => $data['title'],
            'category' => $data['category'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'file_path' => $path,
            'original_filename' => $this->safeOriginalFilename($file->getClientOriginalName()),
            'mime_type' => $file->getMimeType() ?: 'application/pdf',
            'file_size' => $file->getSize(),
            'published_at' => $data['status'] === Document::STATUS_PUBLISHED ? now() : null,
        ]);

        return to_route('admin.documents.index')->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function show(Document $document): Response
    {
        $this->authorize('view', $document);

        return Inertia::render('Admin/Documents/Show', [
            'document' => $document->load('uploader'),
        ]);
    }

    public function edit(Document $document): Response
    {
        $this->authorize('update', $document);

        return Inertia::render('Admin/Documents/Form', [
            'document' => $document,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(DocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $data = $request->validated();
        $payload = [
            'title' => $data['title'],
            'category' => $data['category'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'published_at' => $data['status'] === Document::STATUS_PUBLISHED
                ? ($document->published_at ?? now())
                : null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            Storage::disk('local')->delete($document->file_path);
            $payload = array_merge($payload, [
                'file_path' => $file->store('documents', 'local'),
                'original_filename' => $this->safeOriginalFilename($file->getClientOriginalName()),
                'mime_type' => $file->getMimeType() ?: 'application/pdf',
                'file_size' => $file->getSize(),
            ]);
        }

        $document->update($payload);

        return to_route('admin.documents.show', $document)->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $document->delete();

        return to_route('admin.documents.index')->with('success', 'Dokumen berhasil dihapus.');
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);
        abort_unless($this->isDocumentPath($document->file_path), 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $this->safeOriginalFilename($document->original_filename));
    }

    private function categoryOptions(): array
    {
        return [
            ['value' => Document::CATEGORY_GUIDE, 'label' => 'Pedoman'],
            ['value' => Document::CATEGORY_NOTIFICATION, 'label' => 'Notifikasi'],
            ['value' => Document::CATEGORY_CERTIFICATE_TEMPLATE, 'label' => 'Template Sertifikat'],
            ['value' => Document::CATEGORY_CARD_TEMPLATE, 'label' => 'Template Kartu Anggota'],
            ['value' => Document::CATEGORY_OTHER_TEMPLATE, 'label' => 'Template Dokumen'],
        ];
    }

    private function isDocumentPath(string $path): bool
    {
        return str_starts_with($path, 'documents/')
            && ! str_contains($path, '..')
            && ! str_contains($path, "\0");
    }

    private function safeOriginalFilename(?string $filename): string
    {
        $filename = basename(str_replace('\\', '/', $filename ?: 'dokumen.pdf'));
        $filename = preg_replace('/[\x00-\x1F\x7F]+/', '', $filename) ?: 'dokumen.pdf';

        return Str::limit($filename, 180, '');
    }
}
