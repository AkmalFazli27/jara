<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

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
