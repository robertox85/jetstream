<?php

namespace Database\Factories;

use App\Models\Pratica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scadenza>
 */
class ScadenzaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $stati = ['da_iniziare', 'in_corso', 'completata', 'annullata'];

        return [
            'pratica_id' => Pratica::factory(),
            'user_id' => User::factory(),
            'assigned_to' => User::factory(),
            'data_ora' => $this->faker->dateTimeBetween('now', '+6 months'),
            'motivo' => $this->faker->sentence(),
            'stato' => $this->faker->randomElement($stati),
            'reminder_at' => [
                now()->addDays(7)->format('Y-m-d H:i:s'),
                now()->addDay()->format('Y-m-d H:i:s'),
                now()->addHour()->format('Y-m-d H:i:s'),
            ],
            'email_notification' => true,
            'browser_notification' => true,
        ];
    }

    public function imminente()
    {
        return $this->state(fn (array $attributes) => [
            'data_ora' => $this->faker->dateTimeBetween('now', '+7 days'),
        ]);
    }
}
