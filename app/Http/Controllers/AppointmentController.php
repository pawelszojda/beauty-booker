<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Appointments/Index', [
            'appointments' => Appointment::query()
                ->with([
                    'customer:id,first_name,last_name,phone,email',
                    'service:id,name,duration_minutes,price',
                    'user:id,name,email',
                ])
                ->orderBy('start_time')
                ->get(),
            'customers' => Customer::query()
                ->select(['id', 'first_name', 'last_name', 'phone', 'email'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
            'services' => Service::query()
                ->select(['id', 'name', 'duration_minutes', 'price'])
                ->orderBy('name')
                ->get(),
            'users' => User::query()
                ->select(['id', 'name', 'email'])
                ->orderBy('name')
                ->get(),
            'statuses' => ['oczekująca', 'potwierdzona', 'odwołana'],
        ]);
    }
}
