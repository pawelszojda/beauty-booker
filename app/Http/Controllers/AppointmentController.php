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
        $user = auth()->user();
        $customer = null;

        $appointmentsQuery = Appointment::query()
            ->with([
                'customer:id,first_name,last_name,phone,email',
                'service:id,name,duration_minutes,price',
                'user:id,name,email',
            ])
            ->orderBy('start_time');

        $customersQuery = Customer::query()
            ->select(['id', 'first_name', 'last_name', 'phone', 'email'])
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($user->isCustomer()) {
            $customer = Customer::query()
                ->where('email', $user->email)
                ->first();

            if ($customer) {
                $appointmentsQuery->whereBelongsTo($customer);
                $customersQuery->whereKey($customer->id);
            } else {
                $appointmentsQuery->whereRaw('1 = 0');
                $customersQuery->whereRaw('1 = 0');
            }
        }

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointmentsQuery->get(),
            'customers' => $customersQuery->get(),
            'services' => Service::query()
                ->select(['id', 'name', 'duration_minutes', 'price'])
                ->orderBy('name')
                ->get(),
            'users' => User::query()
                ->select(['id', 'name', 'email', 'role'])
                ->whereIn('role', ['administrator', 'stylist'])
                ->orderBy('name')
                ->get(),
            'statuses' => ['oczekująca', 'potwierdzona', 'odwołana'],
        ]);
    }
}
