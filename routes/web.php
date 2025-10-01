<?php

use App\Http\Controllers\ProfileController;
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
});

// Admin Panel Routes (protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    // Bulk website creation (MUST be before resource route)
    Route::get('/websites/bulk-create', [WebsiteController::class, 'bulkCreate'])->name('websites.bulk-create');
    Route::post('/websites/bulk-store', [WebsiteController::class, 'bulkStore'])->name('websites.bulk-store');
    
    // Website Management Routes
    Route::resource('websites', WebsiteController::class)->except(['show']);
    
    // Manual monitoring check
    Route::post('/websites/check-now', [WebsiteController::class, 'checkNow'])->name('websites.check-now');
});

require __DIR__.'/auth.php';
