<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicOrganizationMemberPhotoController;
use App\Http\Controllers\PublicDirectoryController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Member\MemberProfileController;
use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Admin\MembershipApplicationController as AdminMembershipApplicationController;
use App\Http\Controllers\Member\MembershipApplicationController as MemberMembershipApplicationController;
use App\Http\Controllers\Member\MembershipController as MemberMembershipController;
use App\Http\Controllers\Admin\MembershipRateController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\IntellectualPropertyController as AdminIntellectualPropertyController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\SiteContentController as AdminSiteContentController;
use App\Http\Controllers\Admin\OrganizationMemberController as AdminOrganizationMemberController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Member\InvoiceController as MemberInvoiceController;
use App\Http\Controllers\Member\IntellectualPropertyController as MemberIntellectualPropertyController;
use App\Http\Controllers\Member\DocumentController as MemberDocumentController;
use App\Http\Controllers\Member\SupportController as MemberSupportController;
use App\Http\Controllers\Member\AccountSettingController as MemberAccountSettingController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Member\PaymentController as MemberPaymentController;
use App\Http\Controllers\Admin\MembershipDocumentController as AdminMembershipDocumentController;
use App\Http\Controllers\Member\MembershipDocumentController as MemberMembershipDocumentController;
use App\Http\Controllers\Admin\MembershipDocumentSettingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Article;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Public/Home', [
        'latestArticles' => Article::with('author')
            ->where('status', Article::STATUS_PUBLISHED)
            ->latest('published_at')
            ->limit(3)
            ->get(),
    ]);
})->name('home');

Route::get('/berita', [PublicArticleController::class, 'index'])->name('public.articles.index');
Route::get('/berita/{article:slug}', [PublicArticleController::class, 'show'])->name('public.articles.show');
Route::get('/berita/{article:slug}/gambar', [PublicArticleController::class, 'cover'])->name('public.articles.cover');
Route::get('/profil', [PublicPageController::class, 'profile'])->name('public.profile');
Route::get('/kontak', [PublicPageController::class, 'contact'])->name('public.contact');
Route::get('/struktur-organisasi/{organizationMember}/foto', PublicOrganizationMemberPhotoController::class)->name('public.organization-members.photo');
Route::get('/anggota', [PublicDirectoryController::class, 'members'])->name('public.members.index');
Route::get('/data-hki', [PublicDirectoryController::class, 'intellectualProperties'])->name('public.intellectual-properties.index');

Route::get('/dashboard', function () {
    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('member.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{user}', [AccountController::class, 'show'])->name('accounts.show');
    Route::get('/membership-applications', [AdminMembershipApplicationController::class, 'index'])->name('applications.index');
    Route::get('/membership-applications/{application}', [AdminMembershipApplicationController::class, 'show'])->name('applications.show');
    Route::put('/membership-applications/{application}', [AdminMembershipApplicationController::class, 'update'])->name('applications.update');
    Route::resource('rates', MembershipRateController::class)->except('show');
    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
    Route::put('/payments/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');
    Route::get('/payments/{payment}/proof', [AdminPaymentController::class, 'proof'])->name('payments.proof');
    Route::get('/intellectual-properties/export', [AdminIntellectualPropertyController::class, 'export'])->name('intellectual-properties.export');
    Route::get('/intellectual-properties', [AdminIntellectualPropertyController::class, 'index'])->name('intellectual-properties.index');
    Route::get('/intellectual-properties/{intellectualProperty}', [AdminIntellectualPropertyController::class, 'show'])->name('intellectual-properties.show');
    Route::get('/documents/{document}/download', [AdminDocumentController::class, 'download'])->name('documents.download');
    Route::get('/membership-documents', [AdminMembershipDocumentController::class, 'index'])->name('membership-documents.index');
    Route::get('/membership-documents/{document}/download', [AdminMembershipDocumentController::class, 'download'])->name('membership-documents.download');
    Route::get('/membership-documents/settings', [MembershipDocumentSettingController::class, 'index'])->name('membership-documents.settings');
    Route::put('/membership-documents/settings/{setting}', [MembershipDocumentSettingController::class, 'update'])->name('membership-documents.settings.update');
    Route::resource('documents', AdminDocumentController::class);
    Route::resource('articles', AdminArticleController::class);
    Route::get('/site-contents', [AdminSiteContentController::class, 'index'])->name('site-contents.index');
    Route::get('/site-contents/{siteContent}/edit', [AdminSiteContentController::class, 'edit'])->name('site-contents.edit');
    Route::put('/site-contents/{siteContent}', [AdminSiteContentController::class, 'update'])->name('site-contents.update');
    Route::resource('organization-members', AdminOrganizationMemberController::class)->except(['show']);
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::resource('faqs', AdminFaqController::class)->except(['show']);
    Route::get('/support-tickets', [AdminSupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/{supportTicket}', [AdminSupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::put('/support-tickets/{supportTicket}', [AdminSupportTicketController::class, 'update'])->name('support-tickets.update');
});

Route::middleware(['auth', 'role:member'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', MemberDashboardController::class)->name('dashboard');
    Route::get('/profile', [MemberProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('profile.update');
    Route::get('/membership-applications', [MemberMembershipApplicationController::class, 'index'])->name('applications.index');
    Route::get('/membership', [MemberMembershipController::class, 'index'])->name('membership.index');
    Route::post('/membership-applications', [MemberMembershipApplicationController::class, 'store'])->name('applications.store');
    Route::get('/invoices', [MemberInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [MemberInvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/payments', [MemberPaymentController::class, 'store'])->name('invoices.payments.store');
    Route::resource('intellectual-properties', MemberIntellectualPropertyController::class)
        ->parameters(['intellectual-properties' => 'intellectualProperty']);
    Route::get('/documents', [MemberDocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}/download', [MemberDocumentController::class, 'download'])->name('documents.download');
    Route::get('/membership-documents', [MemberMembershipDocumentController::class, 'index'])->name('membership-documents.index');
    Route::get('/membership-documents/{document}/download', [MemberMembershipDocumentController::class, 'download'])->name('membership-documents.download');
    Route::get('/membership-documents/{document}/preview', [MemberMembershipDocumentController::class, 'preview'])->name('membership-documents.preview');
    Route::get('/support', [MemberSupportController::class, 'index'])->name('support.index');
    Route::post('/support', [MemberSupportController::class, 'store'])->name('support.store');
    Route::get('/support/{supportTicket}', [MemberSupportController::class, 'show'])->name('support.show');
    Route::get('/settings', [MemberAccountSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/notifications', [MemberAccountSettingController::class, 'update'])->name('settings.notifications.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
