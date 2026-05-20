<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $administrator = User::factory()->administrator()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);

        $stylists = User::factory()
            ->stylist()
            ->count(2)
            ->create();

        $staffUsers = $stylists->push($administrator);

        $loginCustomer = Customer::factory()->create([
            'first_name' => 'Customer',
            'last_name' => 'Account',
            'email' => 'customer@example.com',
        ]);

        User::factory()->customer()->create([
            'name' => 'Customer Account',
            'email' => $loginCustomer->email,
        ]);

        $customers = Customer::factory()
            ->count(30)
            ->create()
            ->push($loginCustomer);

        $services = collect([
            ['name' => 'Manicure klasyczny', 'duration_minutes' => 45, 'price' => 80.00],
            ['name' => 'Manicure hybrydowy', 'duration_minutes' => 75, 'price' => 130.00],
            ['name' => 'Pedicure kosmetyczny', 'duration_minutes' => 60, 'price' => 120.00],
            ['name' => 'Regulacja brwi', 'duration_minutes' => 30, 'price' => 45.00],
            ['name' => 'Henna brwi', 'duration_minutes' => 30, 'price' => 55.00],
            ['name' => 'Laminacja rzęs', 'duration_minutes' => 90, 'price' => 180.00],
            ['name' => 'Oczyszczanie twarzy', 'duration_minutes' => 90, 'price' => 220.00],
            ['name' => 'Makijaż okazjonalny', 'duration_minutes' => 120, 'price' => 250.00],
        ])->map(fn (array $service) => Service::factory()->create($service));

        for ($i = 0; $i < 60; $i++) {
            $service = $services->random();
            $startTime = now()
                ->addDays(fake()->numberBetween(1, 30))
                ->setTime(fake()->numberBetween(9, 17), fake()->randomElement([0, 30]));

            Appointment::factory()->create([
                'customer_id' => $customers->random()->id,
                'service_id' => $service->id,
                'user_id' => $staffUsers->random()->id,
                'start_time' => $startTime,
                'end_time' => $startTime->copy()->addMinutes($service->duration_minutes),
                'status' => fake()->randomElement(['oczekująca', 'potwierdzona', 'odwołana']),
                'google_event_id' => fake()->optional(0.35)->uuid(),
            ]);
        }
    }
}
