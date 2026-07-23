<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BaseUserController;
use App\Http\Controllers\TaskCategoryController;
use App\Http\Controllers\TaskDiscussionController;
use App\Http\Controllers\TaskMasterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

Route::middleware('guest')->group(function () {
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
    Route::get('/manager/dashboard', [DashboardController::class, 'index'])->name('manager.dashboard');
});

Route::middleware(['auth', 'role:administrator'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});

Route::middleware(['auth', 'role:administrator,manager'])->group(function () {
    Route::get('task-attachments/{attachment}/preview', [TaskMasterController::class, 'previewAttachment'])
        ->name('task-attachments.preview');
    Route::get('task-masters/{taskMaster}/discussion', [TaskDiscussionController::class, 'index'])
        ->name('task-masters.discussion.index');
    Route::post('task-masters/{taskMaster}/discussion', [TaskDiscussionController::class, 'store'])
        ->name('task-masters.discussion.store');
    Route::resource('task-masters', TaskMasterController::class);
    Route::resource('task-categories', TaskCategoryController::class);
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::resource('departments', DepartmentController::class)->except('show');
    Route::get('base-users/{baseUser}/password', [BaseUserController::class, 'editPassword'])
        ->name('base-users.password.edit');
    Route::put('base-users/{baseUser}/password', [BaseUserController::class, 'updatePassword'])
        ->name('base-users.password.update');
    Route::resource('base-users', BaseUserController::class)
        ->parameters(['base-users' => 'baseUser'])
        ->except('show');
});
