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
        Schema::create('note', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Relazione con la pratica
            // cascade: se la pratica viene eliminata, vengono eliminate anche le relative note
            $table->foreignId('pratica_id')->constrained('pratiche')->onDelete('cascade');

            // Utente che ha creato la nota
            // cascade: se l'utente viene eliminato, vengono eliminate anche le note create
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Utente che ha modificato per ultimo la nota
            // nullable: per le note non ancora modificate
            $table->foreignId('last_edited_by')->nullable()->constrained('users');

            // Oggetto o titolo della nota
            // Es: "Colloquio con cliente", "Spese anticipate", "Strategia difensiva"
            $table->string('oggetto');

            // Contenuto dettagliato della nota
            $table->text('nota');

            // Tipologia della nota
            // - registro_contabile: per registrare movimenti economici
            // - annotazioni: per note generiche sulla pratica
            $table->enum('tipologia', ['registro_contabile', 'annotazioni']);

            // Livello di visibilità della nota
            // - privata: visibile solo all'autore
            // - pubblica: visibile a tutti gli utenti con accesso alla pratica
            $table->enum('visibilita', ['privata', 'pubblica']);

            // created_at e updated_at automatici
            $table->timestamps();

            // Soft delete - non elimina fisicamente il record ma lo marca come eliminato
            $table->softDeletes();

            // INDICI per ottimizzare le performance delle query più comuni

            // Indice composito per filtrare note per pratica e tipologia
            // Es: "tutte le note contabili della pratica X"
            $table->index(['pratica_id', 'tipologia']);

            // Indice composito per filtrare note per pratica e autore
            // Es: "tutte le note create dall'utente Y nella pratica X"
            $table->index(['pratica_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note');
    }
};
