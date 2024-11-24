<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pratica;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class RicalcolaNumeroPratiche extends Command
{
    protected $signature = 'pratiche:ricalcola-numeri';
    protected $description = 'Ricalcola tutti i numeri delle pratiche';

    public function handle()
    {
        $this->info('Inizio ricalcolo numeri pratiche...');

        // Backup dei numeri attuali
        $backup = DB::table('pratiche')->pluck('numero_pratica', 'id');
        Storage::put(
            'pratiche_numeri_backup_'.date('Y_m_d_His').'.json',
            json_encode($backup, JSON_PRETTY_PRINT)
        );

        // 1. Aggiungi colonna temporanea
        if (!Schema::hasColumn('pratiche', 'temp_numero_pratica')) {
            Schema::table('pratiche', function ($table) {
                $table->string('temp_numero_pratica')->nullable();
            });
        }

        try {
            // 2. Genera i nuovi numeri
            $counter = [];
            $pratiche = Pratica::withTrashed()
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $this->info("Trovate {$pratiche->count()} pratiche da aggiornare");

            // 3. Rimuovi il vincolo di unicità
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('ALTER TABLE pratiche DROP INDEX pratiche_numero_pratica_unique');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            foreach ($pratiche as $pratica) {
                $anno = Carbon::parse($pratica->created_at)->year;

                if (!isset($counter[$anno])) {
                    $counter[$anno] = 1;
                }

                $team = Team::find($pratica->team_id);
                $teamName = $team ? $team->name : 'NoTeam';
                $prefissoTipo = config('pratica.numero_pratica.prefissi_tipo.prefissi.default');
                $numeroProgressivo = str_pad($counter[$anno], 3, '0', STR_PAD_LEFT);
                $nuovoNumeroPratica = "{$prefissoTipo}-{$teamName}-{$anno}-{$numeroProgressivo}";

                // Aggiorna direttamente
                DB::table('pratiche')
                    ->where('id', $pratica->id)
                    ->update([
                        'numero_pratica' => $nuovoNumeroPratica,
                        'updated_at' => now()
                    ]);

                $counter[$anno]++;

                $this->info("Aggiornata pratica {$pratica->id} -> {$nuovoNumeroPratica}");
            }

            // 4. Ricrea il vincolo di unicità
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('ALTER TABLE pratiche ADD UNIQUE pratiche_numero_pratica_unique(numero_pratica)');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('Aggiornamento completato con successo!');

        } catch (\Exception $e) {
            $this->error('Errore durante l\'aggiornamento: ' . $e->getMessage());

            // Ricrea il vincolo se necessario
            try {
                $this->recreateUniqueConstraint();
            } catch (\Exception $e2) {
                $this->error('Errore durante il ripristino del vincolo: ' . $e2->getMessage());
            }

            throw $e;
        }
    }

    private function recreateUniqueConstraint()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $constraintExists = DB::select("SHOW INDEXES FROM pratiche WHERE Key_name = 'pratiche_numero_pratica_unique'");
        if (empty($constraintExists)) {
            DB::statement('ALTER TABLE pratiche ADD UNIQUE pratiche_numero_pratica_unique(numero_pratica)');
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}