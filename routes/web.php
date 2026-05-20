<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/dashboard/appointments', [DashboardController::class, 'storeAppointment'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.appointments.store');

Route::put('/dashboard/appointments/{appointment}', [DashboardController::class, 'updateAppointment'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.appointments.update');

Route::delete('/dashboard/appointments/{appointment}', [DashboardController::class, 'destroyAppointment'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.appointments.destroy');

Route::middleware('auth')->group(function () {
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('appointments', AppointmentController::class)->only(['index']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
