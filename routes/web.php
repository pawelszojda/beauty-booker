<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {
    $customer = Customer::query()
        ->where('email', auth()->user()->email)
        ->first();

    $appointmentsQuery = Appointment::query()
        ->with([
            'customer:id,first_name,last_name,phone,email',
            'service:id,name,duration_minutes,price',
            'user:id,name,email',
        ])
        ->orderBy('start_time');

    if ($customer) {
        $appointmentsQuery->whereBelongsTo($customer);
    }

    return Inertia::render('Dashboard', [
        'appointments' => $appointmentsQuery->get(),
        'appointmentScope' => $customer ? 'customer' : 'all',
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('customers', CustomerController::class)->only(['index']);
    Route::resource('services', ServiceController::class)->only(['index']);
    Route::resource('appointments', AppointmentController::class)->only(['index']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
