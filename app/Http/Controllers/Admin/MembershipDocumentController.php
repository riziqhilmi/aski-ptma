<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\IssuedMembershipDocument;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;
class MembershipDocumentController extends Controller
{
    public function index() { return Inertia::render('Admin/MembershipDocuments/Index', ['documents' => IssuedMembershipDocument::with(['user','membership'])->latest('issued_at')->paginate(15)]); }
    public function download(IssuedMembershipDocument $document): StreamedResponse { abort_unless(Storage::disk('local')->exists($document->file_path), 404); return Storage::disk('local')->download($document->file_path, $document->number.'.pdf'); }
}
