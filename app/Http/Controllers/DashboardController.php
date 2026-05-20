<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $customer = $this->customerForUser($user);

        $appointmentsQuery = Appointment::query()
            ->with([
                'customer:id,first_name,last_name,phone,email',
                'service:id,name,duration_minutes,price',
                'user:id,name,email,role',
            ])
            ->orderBy('start_time');

        if ($user->isCustomer()) {
            $customer
                ? $appointmentsQuery->whereBelongsTo($customer)
                : $appointmentsQuery->whereRaw('1 = 0');
        }

        return Inertia::render('Dashboard', [
            'appointments' => $appointmentsQuery->get(),
            'appointmentScope' => $user->isCustomer() ? 'customer' : 'all',
            'services' => Service::query()
                ->select(['id', 'name', 'duration_minutes', 'price'])
                ->orderBy('name')
                ->get(),
            'stylists' => User::query()
                ->select(['id', 'name', 'email', 'role'])
                ->whereIn('role', ['administrator', 'stylist'])
                ->orderBy('name')
                ->get(),
            'customers' => $user->isCustomer()
                ? []
                : Customer::query()
                    ->select(['id', 'first_name', 'last_name', 'phone', 'email'])
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get(),
            'calendarSlots' => $this->calendarSlots($user, $customer),
        ]);
    }

    public function storeAppointment(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'customer_id' => [Rule::requiredIf(! $user->isCustomer()), 'nullable', 'exists:customers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'user_id' => ['required', 'exists:users,id'],
            'start_time' => ['required', 'date', 'after_or_equal:now'],
        ]);

        $stylist = User::query()
            ->whereKey($validated['user_id'])
            ->whereIn('role', ['administrator', 'stylist'])
            ->firstOrFail();

        $service = Service::findOrFail($validated['service_id']);
        $startTime = Carbon::parse($validated['start_time'])->seconds(0);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        $this->validateBusinessHours($startTime, $endTime);

        $customer = $user->isCustomer()
            ? $this->customerForUser($user)
            : Customer::findOrFail($validated['customer_id']);

        if (! $customer) {
            return back()->withErrors([
                'customer_id' => 'Your customer profile is missing. Please contact the salon.',
            ]);
        }

        $hasOverlap = Appointment::query()
            ->whereBelongsTo($stylist, 'user')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($hasOverlap) {
            return back()->withErrors([
                'start_time' => 'This slot is already taken. Please choose another time.',
            ]);
        }

        Appointment::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'user_id' => $stylist->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'oczekująca',
        ]);

        return redirect()->route('dashboard');
    }

    public function updateAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_if(auth()->user()?->isCustomer(), 403);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'user_id' => ['required', 'exists:users,id'],
            'start_time' => ['required', 'date'],
            'status' => ['required', Rule::in(['oczekująca', 'potwierdzona', 'odwołana'])],
        ]);

        $stylist = User::query()
            ->whereKey($validated['user_id'])
            ->whereIn('role', ['administrator', 'stylist'])
            ->firstOrFail();

        $service = Service::findOrFail($validated['service_id']);
        $startTime = Carbon::parse($validated['start_time'])->seconds(0);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        $this->validateBusinessHours($startTime, $endTime);

        $hasOverlap = Appointment::query()
            ->whereKeyNot($appointment->id)
            ->whereBelongsTo($stylist, 'user')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($hasOverlap) {
            return back()->withErrors([
                'start_time' => 'This slot is already taken. Please choose another time.',
            ]);
        }

        $appointment->update([
            'customer_id' => $validated['customer_id'],
            'service_id' => $service->id,
            'user_id' => $stylist->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $validated['status'],
        ]);

        return redirect()->route('dashboard');
    }

    public function destroyAppointment(Appointment $appointment): RedirectResponse
    {
        abort_if(auth()->user()?->isCustomer(), 403);

        $appointment->delete();

        return redirect()->route('dashboard');
    }

    private function customerForUser(User $user): ?Customer
    {
        return Customer::query()
            ->where('email', $user->email)
            ->first();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function calendarSlots(User $user, ?Customer $customer): array
    {
        $stylists = User::query()
            ->select(['id', 'name'])
            ->whereIn('role', ['administrator', 'stylist'])
            ->orderBy('name')
            ->get();

        $rangeStart = now()->startOfDay();
        $rangeEnd = now()->copy()->addDays(14)->endOfDay();

        $appointments = Appointment::query()
            ->with([
                'customer:id,first_name,last_name,phone,email',
                'service:id,name,duration_minutes,price',
                'user:id,name,email,role',
            ])
            ->whereBetween('start_time', [$rangeStart, $rangeEnd])
            ->get()
            ->groupBy('user_id');

        $slots = [];

        for ($day = now()->startOfDay(); $day->lte(now()->addDays(14)->startOfDay()); $day->addDay()) {
            foreach ($stylists as $stylist) {
                for ($slot = $day->copy()->setTime(9, 0); $slot->lt($day->copy()->setTime(18, 0)); $slot->addMinutes(30)) {
                    $slotEnd = $slot->copy()->addMinutes(30);
                    $appointment = $appointments
                        ->get($stylist->id, collect())
                        ->first(fn (Appointment $appointment): bool =>
                            $appointment->start_time->lt($slotEnd) && $appointment->end_time->gt($slot)
                        );

                    if ($appointment && $user->isCustomer() && $appointment->customer_id !== $customer?->id) {
                        continue;
                    }

                    $slots[] = [
                        'id' => $stylist->id.'-'.$slot->format('YmdHi'),
                        'date' => $slot->toDateString(),
                        'time' => $slot->format('H:i'),
                        'start_time' => $slot->toDateTimeString(),
                        'stylist_id' => $stylist->id,
                        'stylist_name' => $stylist->name,
                        'status' => $appointment ? 'taken' : 'free',
                        'appointment' => $appointment ? [
                            'id' => $appointment->id,
                            'status' => $appointment->status,
                            'start_time' => $appointment->start_time,
                            'end_time' => $appointment->end_time,
                            'customer' => $appointment->customer,
                            'service' => $appointment->service,
                        ] : null,
                    ];
                }
            }
        }

        return $slots;
    }

    private function validateBusinessHours(Carbon $startTime, Carbon $endTime): void
    {
        if ($startTime->hour < 9 || $endTime->hour > 18 || ($endTime->hour === 18 && $endTime->minute > 0)) {
            throw ValidationException::withMessages([
                'start_time' => 'Appointment must be between 09:00 and 18:00.',
            ]);
        }
    }
}
