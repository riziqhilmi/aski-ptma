<?php
namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use App\Models\IssuedMembershipDocument;
use App\Services\MembershipDocumentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
class MembershipDocumentController extends Controller
{
    public function index() { return Inertia::render('Member/MembershipDocuments/Index', ['documents' => request()->user()->issuedMembershipDocuments()->latest('issued_at')->paginate(10)->withQueryString()]); }
    public function download(IssuedMembershipDocument $document, MembershipDocumentService $documents): StreamedResponse
    {
        $filename = $this->downloadFilename($document);
        abort_unless($document->user_id === request()->user()->id, 403);
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

    public function preview(IssuedMembershipDocument $document, MembershipDocumentService $documents)
    {
        $filename = $this->downloadFilename($document);
        abort_unless($document->user_id === request()->user()->id, 403);
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
