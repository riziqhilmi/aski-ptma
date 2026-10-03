<?php
namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use App\Models\IssuedMembershipDocument;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
class MembershipDocumentController extends Controller
{
    public function index() { return Inertia::render('Member/MembershipDocuments/Index', ['documents' => request()->user()->issuedMembershipDocuments()->latest('issued_at')->get()]); }
    public function download(IssuedMembershipDocument $document): StreamedResponse { abort_unless($document->user_id === request()->user()->id, 403); abort_unless(Storage::disk('local')->exists($document->file_path), 404); return Storage::disk('local')->download($document->file_path, $document->number.'.pdf'); }
    public function preview(IssuedMembershipDocument $document) { abort_unless($document->user_id === request()->user()->id, 403); abort_unless(Storage::disk('local')->exists($document->file_path), 404); return Storage::disk('local')->response($document->file_path, $document->number.'.pdf', ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']); }
}
