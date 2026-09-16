<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Home Redirection
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

// Guest Routes (Authentication)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Authenticated Routes
Route::middleware('auth')->group(function (): void {
    // Logout Action
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Profile Management (Lihat & Edit Profil)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Protected User Routes (Regular User)
    Route::middleware('role:user')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Lists & Tasks
        Route::get('/lists', [ListController::class, 'index'])->name('lists.index');
        Route::get('/kanban', [ListController::class, 'kanban'])->name('lists.kanban');
        Route::post('/lists', [ListController::class, 'store'])->name('lists.store');
        Route::get('/lists/{list}', [ListController::class, 'show'])->name('lists.show');
        Route::put('/lists/{list}', [ListController::class, 'update'])->name('lists.update');
        Route::delete('/lists/{list}', [ListController::class, 'destroy'])->name('lists.destroy');

        Route::post('/lists/{list}/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });

    // Protected Admin Routes (Administrator Only)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User Management (F-04 s.d F-06)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
