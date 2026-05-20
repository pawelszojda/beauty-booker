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
    $user = auth()->user();
    $customer = null;

    $appointmentsQuery = Appointment::query()
        ->with([
            'customer:id,first_name,last_name,phone,email',
            'service:id,name,duration_minutes,price',
            'user:id,name,email',
        ])
        ->orderBy('start_time');

    if ($user->isCustomer()) {
        $customer = Customer::query()
            ->where('email', $user->email)
            ->first();

        $customer
            ? $appointmentsQuery->whereBelongsTo($customer)
            : $appointmentsQuery->whereRaw('1 = 0');
    }

    return Inertia::render('Dashboard', [
        'appointments' => $appointmentsQuery->get(),
        'appointmentScope' => $user->isCustomer() ? 'customer' : 'all',
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::resource('appointments', AppointmentController::class)->only(['index']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
