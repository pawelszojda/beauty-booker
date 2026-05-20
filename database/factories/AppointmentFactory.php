<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = now()
            ->addDays(fake()->numberBetween(1, 30))
            ->setTime(fake()->numberBetween(9, 17), fake()->randomElement([0, 30]));

        $durationMinutes = fake()->randomElement([30, 45, 60, 75, 90, 120]);

        return [
            'customer_id' => Customer::factory(),
            'service_id' => Service::factory(),
            'user_id' => User::factory(),
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addMinutes($durationMinutes),
            'status' => fake()->randomElement(['oczekująca', 'potwierdzona', 'odwołana']),
            'google_event_id' => fake()->optional(0.35)->uuid(),
        ];
    }
}
