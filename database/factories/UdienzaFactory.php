<?php

namespace Database\Factories;

use App\Models\Pratica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Udienza>
 */
class UdienzaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'pratica_id' => Pratica::factory(),
            'user_id' => User::factory(),
            'assigned_to' => User::factory(),
            'data_ora' => $this->faker->dateTimeBetween('now', '+6 months'),
            'motivo' => $this->faker->sentence(),
            'luogo' => 'Tribunale di ' . $this->faker->city() . ' - Aula ' . $this->faker->numberBetween(1, 20),
            'stato' => $this->faker->randomElement(['da_iniziare', 'in_corso', 'completata', 'annullata']),
            'reminder_at' => [
                now()->addDays(7)->format('Y-m-d H:i:s'),
                now()->addDay()->format('Y-m-d H:i:s'),
                now()->addHour()->format('Y-m-d H:i:s'),
            ],
        ];
    }
}
