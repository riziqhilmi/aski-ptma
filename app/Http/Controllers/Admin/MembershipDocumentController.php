<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\IssuedMembershipDocument;
use App\Services\MembershipDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
class MembershipDocumentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');

        $documents = IssuedMembershipDocument::query()
            ->with(['user', 'membership'])
            ->when(in_array($type, [IssuedMembershipDocument::CERTIFICATE, IssuedMembershipDocument::CARD], true), fn ($query) => $query->where('type', $type))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('membership', fn ($membership) => $membership->where('member_number', 'like', "%{$search}%"));
                });
            })
            ->latest('issued_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/MembershipDocuments/Index', [
            'documents' => $documents,
            'filters' => ['q' => $search, 'type' => $type],
        ]);
    }
    public function download(IssuedMembershipDocument $document, MembershipDocumentService $documents): StreamedResponse
    {
        $filename = $this->downloadFilename($document);
        abort_unless($this->isMembershipDocumentPath($document->file_path), 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        if ($document->type === IssuedMembershipDocument::CARD) {
            $pdf = $documents->renderCardPdf($document->snapshot ?? []);

            return response()->streamDownload(static function () use ($pdf) {
                echo $pdf;
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        return Storage::disk('local')->download($document->file_path, $filename);
    }

    public function preview(IssuedMembershipDocument $document, MembershipDocumentService $documents): Response
    {
        $filename = $this->downloadFilename($document);
        abort_unless($this->isMembershipDocumentPath($document->file_path), 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        if ($document->type === IssuedMembershipDocument::CARD) {
            return response($documents->renderCardPdf($document->snapshot ?? []), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $filename),
            ]);
        }

        return Storage::disk('local')->response($document->file_path, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
        ]);
    }
    private function downloadFilename(IssuedMembershipDocument $document): string { return (Str::slug(str_replace(['/', '\\'], '-', $document->number), '-') ?: 'dokumen-keanggotaan').'.pdf'; }
    private function isMembershipDocumentPath(string $path): bool { return str_starts_with($path, 'membership-documents/') && ! str_contains($path, '..') && ! str_contains($path, '\\0'); }
}
