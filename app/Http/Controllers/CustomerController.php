<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Customers/Index', [
            'customers' => Customer::query()
                ->withCount('appointments')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
        ]);
    }
}
