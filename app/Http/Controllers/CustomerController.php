<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Customers/Index', [
            'customers' => Customer::query()
                ->withCount('appointments')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Customers/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyCustomerAccess();

        Customer::create($this->validatedData($request));

        return redirect()->route('customers.index');
    }

    public function edit(Customer $customer): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->denyCustomerAccess();

        $customer->update($this->validatedData($request));

        return redirect()->route('customers.index');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->denyCustomerAccess();

        DB::transaction(function () use ($customer): void {
            $customer->appointments()->each->delete();
            $customer->delete();
        });

        return redirect()->route('customers.index');
    }

    private function denyCustomerAccess(): void
    {
        abort_if(auth()->user()?->isCustomer(), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
