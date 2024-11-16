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
        Schema::create('udienze', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Relazione con la pratica
            // cascade: se la pratica viene eliminata, vengono eliminate anche le relative udienze
            $table->foreignId('pratica_id')->constrained('pratiche')->onDelete('cascade');

            // Utente che ha inserito l'udienza
            // cascade: se l'utente viene eliminato, vengono eliminate anche le udienze create
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Avvocato assegnato all'udienza
            // nullable: l'udienza può non essere ancora assegnata a un avvocato specifico
            $table->foreignId('assigned_to')->nullable()->constrained('users');

            // Data e ora dell'udienza
            // Formato: YYYY-MM-DD HH:MM:SS
            $table->dateTime('data_ora');

            // Motivo o tipo dell'udienza
            // Es: "Prima udienza", "Escussione testimoni", "Discussione finale"
            $table->string('motivo');

            // Luogo dell'udienza
            // Es: "Tribunale di Milano - Aula 5", "Giudice di Pace - Piano 2"
            $table->string('luogo')->nullable();

            // Stato corrente dell'udienza
            // - da_iniziare: udienza programmata
            // - in_corso: udienza in svolgimento
            // - completata: udienza conclusa
            // - annullata: udienza annullata o rinviata
            $table->enum('stato', ['da_iniziare', 'in_corso', 'completata', 'annullata'])
                ->default('da_iniziare');

            // SISTEMA DI NOTIFICHE

            // Array JSON contenente i timestamp per le notifiche multiple
            // Es: ["2024-03-20 09:00:00", "2024-03-19 09:00:00"]
            // Permette di configurare più promemoria per la stessa udienza
            $table->json('reminder_at')->nullable();

            // Flag per abilitare/disabilitare le notifiche via email
            $table->boolean('email_notification')->default(true);

            // Flag per abilitare/disabilitare le notifiche nel browser
            $table->boolean('browser_notification')->default(true);

            // created_at e updated_at automatici
            $table->timestamps();

            // Soft delete - non elimina fisicamente il record ma lo marca come eliminato
            $table->softDeletes();

            // INDICI per ottimizzare le performance delle query più comuni

            // Indice sulla data per ricerche temporali
            // Es: "tutte le udienze della settimana"
            $table->index('data_ora');

            // Indice composito per filtrare per utente creatore e stato
            // Es: "tutte le udienze completate inserite dall'utente X"
            $table->index(['user_id', 'stato']);

            // Indice composito per filtrare per avvocato assegnato e stato
            // Es: "tutte le udienze da svolgere assegnate all'avvocato Y"
            $table->index(['assigned_to', 'stato']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('udienze');
    }
};
