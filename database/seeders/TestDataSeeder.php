<?php

namespace Database\Seeders;

use App\Models\Anagrafica;
use App\Models\Documento;
use App\Models\Nota;
use App\Models\Pratica;
use App\Models\Scadenza;
use App\Models\Team;
use App\Models\Udienza;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Crea 3 team
        $teams = Team::factory()->count(3)->create();

        // Per ogni team
        $teams->each(function ($team) {
            // Crea 5 pratiche
            Pratica::factory()
                ->count(5)
                ->state(['team_id' => $team->id])
                ->has(
                    Anagrafica::factory()
                        ->count(2)
                        ->assistito()
                        ->state(function (array $attributes, Pratica $pratica) {
                            return [
                                'tipo_relazione' => 'assistito'
                            ];
                        }),
                    'anagrafiche'
                )
                ->has(
                    Anagrafica::factory()
                        ->count(3)
                        ->controparte()
                        ->state(function (array $attributes, Pratica $pratica) {
                            return [
                                'tipo_relazione' => 'controparte'
                            ];
                        }),
                    'anagrafiche'
                )
                ->has(Scadenza::factory()->count(3))
                ->has(Udienza::factory()->count(2))
                ->has(Nota::factory()->count(5))
                ->has(Documento::factory()->count(3))
                ->create();
        });
    }
}