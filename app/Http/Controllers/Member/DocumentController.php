<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $category = $request->string('category')->toString();

        return Inertia::render('Member/Documents/Index', [
            'documents' => Document::query()
                ->where('status', Document::STATUS_PUBLISHED)
                ->when(in_array($category, Document::CATEGORIES, true), fn ($query) => $query->where('category', $category))
                ->latest('published_at')
                ->paginate(10)
                ->withQueryString(),
            'filters' => [
                'category' => $category,
            ],
            'categories' => [
                ['value' => Document::CATEGORY_GUIDE, 'label' => 'Pedoman'],
                ['value' => Document::CATEGORY_NOTIFICATION, 'label' => 'Notifikasi'],
                ['value' => Document::CATEGORY_CERTIFICATE_TEMPLATE, 'label' => 'Template Sertifikat'],
                ['value' => Document::CATEGORY_CARD_TEMPLATE, 'label' => 'Template Kartu Anggota'],
                ['value' => Document::CATEGORY_OTHER_TEMPLATE, 'label' => 'Template Dokumen'],
            ],
        ]);
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_filename);
    }
}
