<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anagrafiche', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Tipologia di anagrafica
            // 'controparte': parte avversa nel procedimento
            // 'assistito': cliente dello studio
            $table->string('type');

            // Tipo di soggetto
            // 'Persona': persona fisica
            // 'Azienda': persona giuridica/società
            $table->string('tipo_utente');

            // DATI ANAGRAFICI

            // Denominazione/Ragione sociale (per aziende)
            $table->string('denominazione')->nullable();

            // Nome e cognome (per persone fisiche)
            $table->string('nome')->nullable();
            $table->string('cognome')->nullable();

            // DATI DI CONTATTO E INDIRIZZO

            // Indirizzo completo
            $table->string('indirizzo')->nullable();
            $table->string('codice_postale', 5)->nullable();
            $table->string('citta')->nullable();
            $table->string('provincia', 2)->nullable(); // Sigla provincia

            // Recapiti telefonici
            $table->string('telefono')->nullable();
            $table->string('cellulare')->nullable();

            // Indirizzi email
            $table->string('email')->unique()->nullable();
            $table->string('pec')->nullable();

            // DATI FISCALI

            // Codice fiscale (16 caratteri)
            $table->string('codice_fiscale', 16)->nullable()->unique();

            // Partita IVA (11 caratteri)
            $table->string('partita_iva', 11)->nullable()->unique();

            // Codice univoco per fatturazione elettronica (7 caratteri)
            $table->string('codice_univoco_destinatario', 7)->nullable();

            // Note/appunti aggiuntivi
            $table->text('nota')->nullable();

            // created_at e updated_at automatici
            $table->timestamps();

            // Soft delete - non elimina fisicamente il record ma lo marca come eliminato
            $table->softDeletes();

            // INDICI per ottimizzare le performance delle query più comuni

            // Indice per filtrare per tipo di anagrafica
            $table->index('type');

            // Indice per filtrare per tipo di soggetto
            $table->index('tipo_utente');

            // Indice composito per ricerca per nome completo
            $table->index(['nome', 'cognome']);

            // Indici per ricerche puntuali
            $table->index('codice_fiscale');
            $table->index('partita_iva');
            $table->index('denominazione');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anagrafiche');
    }
};
