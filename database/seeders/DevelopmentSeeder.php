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


class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        // Crea dati base
        $team = Team::factory()->create([
            'name' => 'Team Principale'
        ]);

        // Crea pratiche in stati diversi
        $praticaAperta = Pratica::factory()
            ->aperta()
            ->state(['team_id' => $team->id])
            ->create([
                'nome' => 'Pratica Test Aperta'
            ]);

        $praticaChiusa = Pratica::factory()
            ->chiusa()
            ->state(['team_id' => $team->id])
            ->create([
                'nome' => 'Pratica Test Chiusa'
            ]);

        // Aggiungi scadenze imminenti
        Scadenza::factory()
            ->count(3)
            ->state([
                'pratica_id' => $praticaAperta->id,
                'data_ora' => now()->addDays(2)
            ])
            ->create();

        // Aggiungi udienze future
        Udienza::factory()
            ->count(2)
            ->state([
                'pratica_id' => $praticaAperta->id,
                'data_ora' => now()->addWeeks(1)
            ])
            ->create();

        // Aggiungi documenti
        Documento::factory()
            ->count(5)
            ->state([
                'pratica_id' => $praticaAperta->id
            ])
            ->create();
    }
}