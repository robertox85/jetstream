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
        $teams = Team::factory()
            ->count(3)
            ->create();

        // Per ogni team
        $teams->each(function ($team) {
            // Crea 10 pratiche per team
            Pratica::factory()
                ->count(10)
                ->state(['team_id' => $team->id])
                // Non specificare tipo_relazione qui nelle anagrafiche
                ->create()
                ->each(function ($pratica) {
                    // Crea e collega gli assistiti
                    Anagrafica::factory()
                        ->count(rand(1, 4))
                        ->assistito()
                        ->create()
                        ->each(function ($anagrafica) use ($pratica) {
                            // Collega l'anagrafica alla pratica specificando il tipo di relazione
                            $pratica->anagrafiche()->attach($anagrafica->id, [
                                'tipo_relazione' => 'assistito'
                            ]);
                        });

                    // Crea e collega le controparti
                    Anagrafica::factory()
                        ->count(rand(1, 3))
                        ->controparte()
                        ->create()
                        ->each(function ($anagrafica) use ($pratica) {
                            // Collega l'anagrafica alla pratica specificando il tipo di relazione
                            $pratica->anagrafiche()->attach($anagrafica->id, [
                                'tipo_relazione' => 'controparte'
                            ]);
                        });

                    // Aggiungi le altre relazioni
                    Scadenza::factory()->count(5)->create(['pratica_id' => $pratica->id]);
                    Udienza::factory()->count(3)->create(['pratica_id' => $pratica->id]);
                    Nota::factory()
                        ->count(15)
                        ->state(function (array $attributes) {
                            return [
                                'visibilita' => rand(0, 1) ? 'pubblica' : 'privata'
                            ];
                        })
                        ->create(['pratica_id' => $pratica->id]);
                });
        });

        // Crea pratiche in stati diversi
        Pratica::factory()
            ->count(5)
            ->state(['stato' => 'chiuso'])
            ->create();

        Pratica::factory()
            ->count(3)
            ->state(['stato' => 'sospeso'])
            ->create();

        // Pratica con molti documenti
        Pratica::factory()
            ->create()
            ->each(function ($pratica) {
                Documento::factory()
                    ->count(100)
                    ->create(['pratica_id' => $pratica->id]);
            });
    }
}