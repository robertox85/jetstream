<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pratica;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RicalcolaNumeroPratiche extends Command
{
    protected $signature = 'pratiche:ricalcola-numeri';
    protected $description = 'Ricalcola tutti i numeri delle pratiche';

    public function handle()
    {
        $this->info('Inizio ricalcolo numeri pratiche...');

        DB::transaction(function () {

            try {
                $counter = [];

                // Ottieni tutte le pratiche ordinate
                $pratiche = Pratica::withTrashed()
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get();

                foreach ($pratiche as $pratica) {
                    $anno = Carbon::parse($pratica->created_at)->year;

                    if (!isset($counter[$anno])) {
                        $counter[$anno] = 1;
                    }

                    $team = Team::find($pratica->team_id);
                    $teamName = $team ? $team->name : 'NoTeam';

                    $numeroProgressivo = str_pad($counter[$anno], 3, '0', STR_PAD_LEFT);
                    $nuovoNumeroPratica = "STD-{$teamName}-{$anno}-{$numeroProgressivo}";

                    // Aggiorna direttamente usando la query builder
                    DB::table('pratiche')
                        ->where('id', $pratica->id)
                        ->update([
                            'numero_pratica' => $nuovoNumeroPratica,
                            'updated_at' => now()
                        ]);

                    $counter[$anno]++;

                    $this->info("Aggiornata pratica {$pratica->id} -> {$nuovoNumeroPratica}");
                }

                // Ricrea il vincolo di unicità
               // DB::statement('ALTER TABLE pratiche ADD UNIQUE pratiche_numero_pratica_unique(numero_pratica)');

                $this->info('Aggiornamento completato con successo!');

            } catch (\Exception $e) {
                $this->error('Errore durante l\'aggiornamento: ' . $e->getMessage());
            }
        });
    }
}