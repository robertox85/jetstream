<?php

namespace Database\Factories;

use App\Models\Pratica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Nota>
 */
class NotaFactory extends Factory
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
            'last_edited_by' => User::factory(),
            'oggetto' => $this->faker->sentence(),
            'nota' => $this->faker->paragraph(3),
            'tipologia' => $this->faker->randomElement(['registro_contabile', 'annotazioni']),
            'visibilita' => $this->faker->randomElement(['privata', 'pubblica']),
        ];
    }
}
