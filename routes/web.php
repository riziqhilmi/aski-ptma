<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Member\MemberProfileController;
use App\Http\Controllers\Admin\MembershipApplicationController as AdminMembershipApplicationController;
use App\Http\Controllers\Member\MembershipApplicationController as MemberMembershipApplicationController;
use App\Http\Controllers\Admin\MembershipRateController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\IntellectualPropertyController as AdminIntellectualPropertyController;
use App\Http\Controllers\Member\InvoiceController as MemberInvoiceController;
use App\Http\Controllers\Member\IntellectualPropertyController as MemberIntellectualPropertyController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Member\PaymentController as MemberPaymentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    return Inertia::render('Public/Home');
})->name('home');

Route::get('/dashboard', function () {
    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('member.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');
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
});

Route::middleware(['auth', 'role:member'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Member/Dashboard'))->name('dashboard');
    Route::get('/profile', [MemberProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [MemberProfileController::class, 'update'])->name('profile.update');
    Route::get('/membership-applications', [MemberMembershipApplicationController::class, 'index'])->name('applications.index');
    Route::post('/membership-applications', [MemberMembershipApplicationController::class, 'store'])->name('applications.store');
    Route::get('/invoices', [MemberInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [MemberInvoiceController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/payments', [MemberPaymentController::class, 'store'])->name('invoices.payments.store');
    Route::resource('intellectual-properties', MemberIntellectualPropertyController::class)
        ->parameters(['intellectual-properties' => 'intellectualProperty']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
