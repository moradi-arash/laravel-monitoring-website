<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::patch('/settings/cron', [SettingsController::class, 'updateCron'])
        ->middleware('admin')
        ->name('settings.cron.update');
});

// Admin Panel Routes (protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    // CSV Import/Export routes (MUST be before resource route)
    Route::get('/websites/import', [WebsiteController::class, 'importForm'])->name('websites.import');
    Route::post('/websites/import', [WebsiteController::class, 'importCsv'])->name('websites.import.store');
    Route::get('/websites/export', [WebsiteController::class, 'exportCsv'])->name('websites.export');
    Route::get('/websites/template', [WebsiteController::class, 'downloadTemplate'])->name('websites.template');
    
    // Bulk website creation (MUST be before resource route)
    Route::get('/websites/bulk-create', [WebsiteController::class, 'bulkCreate'])->name('websites.bulk-create');
    Route::post('/websites/bulk-store', [WebsiteController::class, 'bulkStore'])->name('websites.bulk-store');
    
    // Website Management Routes
    Route::resource('websites', WebsiteController::class)->except(['show']);
    
    // Manual monitoring check
    Route::post('/websites/check-now', [WebsiteController::class, 'checkNow'])->name('websites.check-now');
});

// Admin-only routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // User Management Routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
