<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskCategoryController;
use App\Http\Controllers\TaskMasterController;
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/change-password', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::put('/change-password', [ProfileController::class, 'updatePassword'])->name('password.update');
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::view('/manager/dashboard', 'manager.dashboard')->name('manager.dashboard');
});

Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::resource('task-categories', TaskCategoryController::class)->except(['show']);
    Route::get('task-attachments/{attachment}/preview', [TaskMasterController::class, 'previewAttachment'])
        ->name('task-attachments.preview');
    Route::resource('task-masters', TaskMasterController::class);
});
