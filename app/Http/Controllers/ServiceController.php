<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Services/Index', [
            'services' => Service::query()
                ->withCount('appointments')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Services/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyCustomerAccess();

        Service::create($this->validatedData($request));

        return redirect()->route('services.index');
    }

    public function edit(Service $service): Response
    {
        $this->denyCustomerAccess();

        return Inertia::render('Services/Edit', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $this->denyCustomerAccess();

        $service->update($this->validatedData($request));

        return redirect()->route('services.index');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->denyCustomerAccess();

        DB::transaction(function () use ($service): void {
            $service->appointments()->delete();
            $service->delete();
        });

        return redirect()->route('services.index');
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
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);
    }
}
