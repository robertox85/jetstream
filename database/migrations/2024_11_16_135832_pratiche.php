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
        Schema::create('pratiche', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Numero identificativo univoco della pratica, può essere NULL se non ancora assegnato
            // Es: "2024/001", "2024/002", etc.
            $table->string('numero_pratica')->unique()->nullable();

            // Nome descrittivo della pratica
            // Es: "Causa Rossi vs Bianchi", "Divorzio coniugi Verdi"
            $table->string('nome');

            // Tipologia della pratica
            // Es: "Civile", "Penale", "Amministrativo", "Stragiudiziale"
            $table->string('tipologia')->nullable();

            // Competenza territoriale
            // Es: "Tribunale di Milano", "Giudice di Pace di Roma"
            $table->string('competenza')->nullable();

            // Numero di ruolo del procedimento
            // Es: "RG 1234/2024"
            $table->string('ruolo_generale')->nullable();

            // Nome del giudice assegnato
            // Es: "Dott. Mario Rossi"
            $table->string('giudice')->nullable();

            // Stato corrente della pratica
            // - aperto: pratica in corso
            // - chiuso: pratica conclusa
            // - sospeso: pratica temporaneamente sospesa
            $table->enum('stato', ['aperto', 'chiuso', 'sospeso'])->nullable();

            // Campo per note o riferimenti aggiuntivi in formato libero
            $table->text('altri_riferimenti')->nullable();

            // Priorità della pratica
            // Es: "alta", "media", "bassa"
            $table->text('priority')->nullable();

            // Data di apertura della pratica
            // Formato: YYYY-MM-DD
            $table->text('data_apertura')->nullable();

            // Riferimento al team assegnato alla pratica
            // In caso di eliminazione del team, il campo viene impostato a NULL
            $table->foreignId('team_id')->constrained('teams')->onDelete('set null')->nullable();

            // created_at e updated_at automatici
            $table->timestamps();

            // Soft delete - non elimina fisicamente il record ma lo marca come eliminato
            $table->softDeletes();

            // INDICI per ottimizzare le performance delle query più comuni

            // Indice singolo sullo stato per velocizzare le ricerche per stato
            $table->index('stato');

            // Indice sulla data di creazione per ordinamenti temporali
            $table->index('created_at');

            // Indice composito per ricerche filtrate per team e stato
            // Es: "tutte le pratiche aperte del team X"
            $table->index(['team_id', 'stato']);

            // Indice sul numero pratica per ricerche puntuali
            $table->index('numero_pratica');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pratiche');
    }
};
