<?php

use App\Http\Controllers\BusinessRegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'headline' => 'Collect Payments. Settle Faster. Grow Your Business.',
    ]);
});

Route::get('/business/register', [BusinessRegistrationController::class, 'index'])
    ->name('business.register');

Route::post('/business/register', [BusinessRegistrationController::class, 'store'])
    ->name('business.register.store');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
