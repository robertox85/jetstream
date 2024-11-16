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
        // Tabella principale dei documenti
        Schema::create('documenti', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Relazione con la pratica
            // cascade: se la pratica viene eliminata, vengono eliminati anche i relativi documenti
            $table->foreignId('pratica_id')->constrained('pratiche')->onDelete('cascade');

            // Utente che ha caricato il documento
            // cascade: se l'utente viene eliminato, vengono eliminati anche i documenti caricati
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Categoria del documento
            // nullable: il documento può non appartenere a nessuna categoria
            $table->foreignId('categoria_id')->nullable()->constrained('categorie_documenti');

            // Percorso del file nel filesystem
            // Es: "documenti/pratica_123/filename.pdf"
            $table->string('file_path');

            // Nome originale del file caricato
            // Es: "Comparsa_conclusionale.pdf"
            $table->string('original_name');

            // Tipo MIME del file
            // Es: "application/pdf", "image/jpeg"
            $table->string('mime_type');

            // Dimensione del file in bytes
            // unsigned: la dimensione non può essere negativa
            $table->integer('size')->unsigned();

            // Descrizione opzionale del documento
            $table->string('descrizione')->nullable();

            // Hash del file per verifica integrità e duplicati
            // Es: SHA-256 hash del contenuto del file
            $table->string('hash_file')->nullable();

            // created_at e updated_at automatici
            $table->timestamps();

            // Soft delete - non elimina fisicamente il record ma lo marca come eliminato
            $table->softDeletes();

            // INDICI per ottimizzare le performance delle query più comuni

            // Indice composito per documenti di una pratica ordinati per data
            $table->index(['pratica_id', 'created_at']);

            // Indice composito per documenti caricati da un utente ordinati per data
            $table->index(['user_id', 'created_at']);

            // Indice per filtrare per tipo di file
            $table->index('mime_type');

            // Indice per ordinamento temporale
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documenti');
    }
};
