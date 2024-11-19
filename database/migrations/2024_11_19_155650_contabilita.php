<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabella per la contabilità
        Schema::create('contabilita', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Relazione con la pratica
            // cascade: se la pratica viene eliminata, vengono eliminate anche i movimenti contabili
            $table->foreignId('pratica_id')->constrained('pratiche')->onDelete('cascade');

            // Utente che ha creato il movimento contabile
            // cascade: se l'utente viene eliminato, vengono eliminate anche i movimenti contabili creati
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Utente che ha modificato per ultimo il movimento contabile
            // nullable: per i movimenti contabili non ancora modificati
            $table->foreignId('last_edited_by')->nullable()->constrained('users');

            // Data del movimento contabile
            $table->date('data')->nullable();

            // Descrizione del movimento contabile
            // Es: "Fattura n. 1234", "Pagamento fattura n. 1234", "Rimborso spese"
            $table->string('descrizione');

            // Importo del movimento contabile
            $table->decimal('importo', 10, 2)->nullable();

            // Tipo di movimento contabile
            // - entrata: per movimenti di denaro in entrata
            // - uscita: per movimenti di denaro in uscita
            $table->enum('tipo', ['entrata', 'uscita']);

            // created_at e updated_at automatici
            $table->timestamps();

            // Soft delete - non elimina fisicamente il record ma lo marca come eliminato
            $table->softDeletes();

            // INDICI per ottimizzare le performance delle query più comuni

            // Indice composito per filtrare movimenti contabili per pratica e tipo
            // Es: "tutti i movimenti contabili in uscita della pratica X"
            $table->index(['pratica_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabilita');
    }
};
