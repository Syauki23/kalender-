<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;

// ─── Public ────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ─── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Dashboard (Admin & Editor) ────────────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ─── Events API ────────────────────────────────────────────────────────────────
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/events',          [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}',  [EventController::class, 'show'])->name('events.show');
    Route::post('/events',          [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}',   [EventController::class, 'update'])->name('events.update');
    Route::patch('/events/{event}', [EventController::class, 'update'])->name('events.update.patch');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
});

// ─── Admin API (User Management) ───────────────────────────────────────────────
Route::prefix('api/admin')->name('api.admin.')->group(function () {
    Route::get('/users',           [AdminController::class, 'users'])->name('users.index');
    Route::post('/users',          [AdminController::class, 'addUser'])->name('users.store');
    Route::delete('/users/{user}', [AdminController::class, 'removeUser'])->name('users.destroy');
});
