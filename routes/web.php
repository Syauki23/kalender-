<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WaManagerController;

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
    Route::get('/whatsapp-contacts/all', [App\Http\Controllers\WhatsappContactController::class, 'getAllContacts'])->name('whatsapp-contacts.all');

    // Wa Manager API
    Route::get('/wa-manager/events', [WaManagerController::class, 'getEvents'])->name('wa-manager.events');
    Route::get('/wa-manager/events/{id}', [WaManagerController::class, 'getEventDetails'])->name('wa-manager.details');
    Route::post('/wa-manager/events/{id}/reminders', [WaManagerController::class, 'saveReminders'])->name('wa-manager.save');
});

// ─── Admin API (Management) ────────────────────────────────────────────────────
Route::prefix('api/admin')->name('api.admin.')->group(function () {
    // Departments
    Route::get('/departments',           [AdminController::class, 'departments'])->name('departments.index');
    Route::post('/departments',          [AdminController::class, 'addDepartment'])->name('departments.store');
    Route::put('/departments/{department}', [AdminController::class, 'updateDepartment'])->name('departments.update');
    Route::delete('/departments/{department}', [AdminController::class, 'removeDepartment'])->name('departments.destroy');

    // Users
    Route::get('/users',           [AdminController::class, 'users'])->name('users.index');
    Route::post('/users',          [AdminController::class, 'addUser'])->name('users.store');
    Route::put('/users/{user}',    [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'removeUser'])->name('users.destroy');

    // WhatsApp Contacts
    Route::get('/departments/{department}/whatsapp-contacts', [App\Http\Controllers\WhatsappContactController::class, 'getContacts'])->name('whatsapp-contacts.index');
    Route::post('/whatsapp-contacts',          [App\Http\Controllers\WhatsappContactController::class, 'store'])->name('whatsapp-contacts.store');
    Route::put('/whatsapp-contacts/{contact}', [App\Http\Controllers\WhatsappContactController::class, 'update'])->name('whatsapp-contacts.update');
    Route::delete('/whatsapp-contacts/{contact}', [App\Http\Controllers\WhatsappContactController::class, 'destroy'])->name('whatsapp-contacts.destroy');
});

// ─── Web Views for Contacts ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/whatsapp-contacts', [App\Http\Controllers\WhatsappContactController::class, 'index'])->name('whatsapp-contacts.view');
    Route::get('/wa-manager', [WaManagerController::class, 'index'])->name('wa-manager.view');
});
