<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Manicure klasyczny',
                'Manicure hybrydowy',
                'Pedicure kosmetyczny',
                'Regulacja brwi',
                'Henna brwi',
                'Laminacja rzęs',
                'Depilacja woskiem',
                'Masaż twarzy',
                'Oczyszczanie twarzy',
                'Makijaż okazjonalny',
            ]),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 75, 90, 120]),
            'price' => fake()->optional(0.9)->randomFloat(2, 50, 350),
        ];
    }
}
