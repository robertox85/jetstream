<?php

namespace Database\Factories;

use App\Models\CategoriaDocumento;
use App\Models\Pratica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documento>
 */
class DocumentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $fileName = $this->faker->word() . '.pdf';

        return [
            'pratica_id' => Pratica::factory(),
            'user_id' => User::factory(),
            'categoria_id' => CategoriaDocumento::factory(),
            'file_path' => 'documenti/' . $fileName,
            'original_name' => $fileName,
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'descrizione' => $this->faker->sentence(),
            'hash_file' => hash('sha256', $this->faker->unique()->uuid()),
        ];
    }
}
