<?php

use App\Http\Controllers\BusinessRegistrationController;
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
