<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicDocumentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\WelcomeController::class, '__invoke']);

Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Document routes
    Route::resource('documents', DocumentController::class);
    Route::patch('/documents/{document}/complete', [DocumentController::class, 'markAsComplete'])->name('documents.complete');
    Route::patch('/documents/{document}/paid', [DocumentController::class, 'markAsPaid'])->name('documents.paid');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/{document}/share', [DocumentController::class, 'share'])->name('documents.share');
    Route::delete('/documents/{document}/share', [DocumentController::class, 'revokeShare'])->name('documents.revoke-share');

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [\App\Http\Controllers\Admin\UserController::class, 'updateRole'])->name('users.update-role');
        Route::patch('/users/{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});

// Public document access (no auth required)
Route::get('/shared/document/{token}', [PublicDocumentController::class, 'show'])->name('public.document.show');
Route::get('/shared/document/{token}/download', [PublicDocumentController::class, 'download'])->name('public.document.download');

require __DIR__ . '/auth.php';
