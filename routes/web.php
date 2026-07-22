<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::view('/manager/dashboard', 'manager.dashboard')->name('manager.dashboard');
});

Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
});
