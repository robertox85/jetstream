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
        Schema::create('activity_logs', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Utente che ha eseguito l'azione
            $table->foreignId('user_id')->constrained('users');

            // Relazione polimorfica con l'entità modificata
            // Crea due colonne:
            // - loggable_type: nome del model (es: 'App\Models\Pratica')
            // - loggable_id: ID dell'entità modificata
            $table->morphs('loggable');

            // Tipo di azione eseguita
            // - create: creazione nuovo record
            // - update: modifica record esistente
            // - delete: eliminazione record
            // - restore: ripristino record eliminato
            $table->string('action');

            // Valori precedenti alla modifica in formato JSON
            // Es: {"nome": "Vecchio nome", "stato": "aperto"}
            $table->json('old_values')->nullable();

            // Nuovi valori dopo la modifica in formato JSON
            // Es: {"nome": "Nuovo nome", "stato": "chiuso"}
            $table->json('new_values')->nullable();

            // created_at e updated_at automatici
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
