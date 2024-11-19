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
        // Tabella per le lavorazioni
        Schema::create('lavorazioni', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Relazione con la pratica
            // cascade: se la pratica viene eliminata, vengono eliminate anche le lavorazioni
            $table->foreignId('pratica_id')->constrained('pratiche')->onDelete('cascade');

            // Utente che ha creato la lavorazione
            // cascade: se l'utente viene eliminato, vengono eliminate anche le lavorazioni create
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Utente che ha modificato per ultimo la lavorazione
            // nullable: per le lavorazioni non ancora modificate
            $table->foreignId('last_edited_by')->nullable()->constrained('users');

            // Data di inizio lavorazione
            $table->date('data_inizio')->nullable();

            // Data di fine lavorazione
            $table->date('data_fine')->nullable();

            // Descrizione della lavorazione
            // Es: "Analisi del caso", "Preparazione documenti", "Colloquio con cliente"
            $table->string('descrizione');

            // created_at e updated_at automatici
            $table->timestamps();

            // Soft delete - non elimina fisicamente il record ma lo marca come eliminato
            $table->softDeletes();

            // INDICI per ottimizzare le performance delle query più comuni

            // Indice composito per filtrare lavorazioni per pratica e data di inizio
            // Es: "tutte le lavorazioni iniziate nella pratica X"
            $table->index(['pratica_id', 'data_inizio']);

            // Indice composito per filtrare lavorazioni per pratica e data di fine
            // Es: "tutte le lavorazioni finite nella pratica X"
            $table->index(['pratica_id', 'data_fine']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lavorazioni');
    }
};
