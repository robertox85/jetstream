<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pratica>
 */
class PraticaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $stati = ['aperto', 'chiuso', 'sospeso'];
        $tipologie = ['Civile', 'Penale', 'Amministrativo', 'Stragiudiziale'];
        $anno = date('Y');

        return [
            'numero_pratica' => $anno . '/' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'nome' => $this->faker->sentence(3),
            'tipologia' => $this->faker->randomElement($tipologie),
            'competenza' => 'Tribunale di ' . $this->faker->city(),
            'ruolo_generale' => 'RG ' . $this->faker->numberBetween(1000, 9999) . '/' . $anno,
            'giudice' => 'Dott. ' . $this->faker->name(),
            'stato' => $this->faker->randomElement($stati),
            'altri_riferimenti' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement(['alta', 'media', 'bassa']),
            'data_apertura' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'team_id' => Team::factory(),  // Assicurati di avere una TeamFactory
        ];
    }

    // Stati specifici che puoi concatenare
    public function aperta()
    {
        return $this->state(fn (array $attributes) => [
            'stato' => 'aperto',
        ]);
    }

    public function chiusa()
    {
        return $this->state(fn (array $attributes) => [
            'stato' => 'chiuso',
        ]);
    }
}
