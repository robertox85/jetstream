<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Anagrafica>
 */
class AnagraficaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $isAzienda = $this->faker->boolean(30); // 30% probabilità che sia un'azienda

        return [
            'type' => $this->faker->randomElement(['controparte', 'assistito']),
            'tipo_utente' => $isAzienda ? 'Azienda' : 'Persona',
            'denominazione' => $isAzienda ? $this->faker->company() : null,
            'nome' => $isAzienda ? null : $this->faker->firstName(),
            'cognome' => $isAzienda ? null : $this->faker->lastName(),
            'indirizzo' => $this->faker->streetAddress(),
            'codice_postale' => $this->faker->numerify('#####'),
            'citta' => $this->faker->city(),
            'provincia' => $this->faker->randomElement(['MI', 'RM', 'TO', 'NA', 'FI']),
            'telefono' => $this->faker->phoneNumber(),
            'cellulare' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'pec' => $this->faker->unique()->safeEmail(),
            'codice_fiscale' => $isAzienda ? null : $this->faker->regexify('[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]'),
            'partita_iva' => $isAzienda ? $this->faker->numerify('###########') : null,
            'codice_univoco_destinatario' => $isAzienda ? $this->faker->regexify('[A-Z0-9]{7}') : null,
            'nota' => $this->faker->paragraph(),
        ];
    }

    public function azienda()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_utente' => 'Azienda',
                'denominazione' => $this->faker->company(),
                'nome' => null,
                'cognome' => null,
                'partita_iva' => $this->faker->numerify('###########'),
                'codice_univoco_destinatario' => $this->faker->regexify('[A-Z0-9]{7}'),
            ];
        });
    }

    public function persona()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_utente' => 'Persona',
                'denominazione' => null,
                'nome' => $this->faker->firstName(),
                'cognome' => $this->faker->lastName(),
                'codice_fiscale' => $this->faker->regexify('[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]'),
            ];
        });
    }

    public function assistito()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'assistito',
        ]);
    }

    public function controparte()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'controparte',
        ]);
    }
}
