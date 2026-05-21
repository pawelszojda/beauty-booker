<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->isAdministrator(), 403);

        $validated = $request->validate([
            'period' => ['nullable', 'integer', Rule::in([7, 14, 21, 30, 60, 90])],
            'stylist_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $period = (int) ($validated['period'] ?? 30);
        $stylistId = $validated['stylist_id'] ?? null;
        $startDate = now()->copy()->subDays($period - 1)->startOfDay();
        $endDate = now()->copy()->endOfDay();

        $appointments = Appointment::query()
            ->with([
                'customer:id,first_name,last_name,email,phone',
                'service:id,name,duration_minutes,price',
                'user:id,name,email,role',
            ])
            ->whereBetween('start_time', [$startDate, $endDate])
            ->when($stylistId, fn ($query) => $query->where('user_id', $stylistId))
            ->orderByDesc('start_time')
            ->get();

        $activeAppointments = $appointments->where('status', '!=', 'odwołana');
        $total = $appointments->count();
        $confirmed = $appointments->where('status', 'potwierdzona')->count();
        $cancelled = $appointments->where('status', 'odwołana')->count();
        $pending = $appointments->where('status', 'oczekująca')->count();
        $revenue = $activeAppointments->sum(fn (Appointment $appointment): float => (float) ($appointment->service?->price ?? 0));

        $daily = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(function ($date) use ($appointments): array {
                $dayAppointments = $appointments->filter(fn (Appointment $appointment): bool => $appointment->start_time->isSameDay($date));

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->format('d.m'),
                    'appointments' => $dayAppointments->count(),
                    'revenue' => $dayAppointments
                        ->where('status', '!=', 'odwołana')
                        ->sum(fn (Appointment $appointment): float => (float) ($appointment->service?->price ?? 0)),
                ];
            })
            ->values();

        return Inertia::render('Reports/Index', [
            'filters' => [
                'period' => $period,
                'stylist_id' => $stylistId ? (int) $stylistId : null,
            ],
            'periodOptions' => [7, 14, 21, 30, 60, 90],
            'stylists' => User::query()
                ->select(['id', 'name', 'email', 'role'])
                ->whereIn('role', ['administrator', 'stylist'])
                ->orderBy('name')
                ->get(),
            'summary' => [
                'total' => $total,
                'confirmed' => $confirmed,
                'pending' => $pending,
                'cancelled' => $cancelled,
                'revenue' => round($revenue, 2),
                'averagePerDay' => round($total / $period, 1),
                'confirmationRate' => $total > 0 ? round(($confirmed / $total) * 100, 1) : 0,
            ],
            'charts' => [
                'daily' => $daily,
                'status' => [
                    ['label' => 'oczekująca', 'value' => $pending],
                    ['label' => 'potwierdzona', 'value' => $confirmed],
                    ['label' => 'odwołana', 'value' => $cancelled],
                ],
            ],
            'tables' => [
                'stylists' => $appointments
                    ->groupBy('user_id')
                    ->map(fn ($items) => [
                        'name' => $items->first()->user?->name ?? '—',
                        'appointments' => $items->count(),
                        'confirmed' => $items->where('status', 'potwierdzona')->count(),
                        'cancelled' => $items->where('status', 'odwołana')->count(),
                        'revenue' => round($items
                            ->where('status', '!=', 'odwołana')
                            ->sum(fn (Appointment $appointment): float => (float) ($appointment->service?->price ?? 0)), 2),
                    ])
                    ->sortByDesc('appointments')
                    ->values(),
                'services' => $appointments
                    ->groupBy('service_id')
                    ->map(fn ($items) => [
                        'name' => $items->first()->service?->name ?? '—',
                        'appointments' => $items->count(),
                        'revenue' => round($items
                            ->where('status', '!=', 'odwołana')
                            ->sum(fn (Appointment $appointment): float => (float) ($appointment->service?->price ?? 0)), 2),
                    ])
                    ->sortByDesc('appointments')
                    ->values(),
                'recentAppointments' => $appointments
                    ->take(10)
                    ->map(fn (Appointment $appointment) => [
                        'id' => $appointment->id,
                        'start_time' => $appointment->start_time,
                        'customer' => trim(($appointment->customer?->first_name ?? '').' '.($appointment->customer?->last_name ?? '')) ?: '—',
                        'service' => $appointment->service?->name ?? '—',
                        'stylist' => $appointment->user?->name ?? '—',
                        'status' => $appointment->status,
                        'price' => (float) ($appointment->service?->price ?? 0),
                    ])
                    ->values(),
            ],
        ]);
    }
}
