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
        Schema::create('scadenze', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Relazione con la pratica
            // cascade: se la pratica viene eliminata, vengono eliminate anche le relative scadenze
            $table->foreignId('pratica_id')->constrained('pratiche')->onDelete('cascade');

            // Utente che ha creato la scadenza
            // cascade: se l'utente viene eliminato, vengono eliminate anche le scadenze create
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Utente a cui è assegnata la scadenza
            // nullable: la scadenza può non essere assegnata a nessuno
            $table->foreignId('assigned_to')->nullable()->constrained('users');

            // Data e ora della scadenza
            // Formato: YYYY-MM-DD HH:MM:SS
            $table->dateTime('data_ora');

            // Descrizione o motivo della scadenza
            // Es: "Deposito memoria", "Termine per il pagamento", etc.
            $table->string('motivo');

            // Stato corrente della scadenza
            // - da_iniziare: scadenza creata ma non ancora in lavorazione
            // - in_corso: scadenza in lavorazione
            // - completata: scadenza completata con successo
            // - annullata: scadenza annullata o non più necessaria
            $table->enum('stato', ['da_iniziare', 'in_corso', 'completata', 'annullata'])
                ->default('da_iniziare');

            // SISTEMA DI NOTIFICHE

            // Array JSON contenente i timestamp per le notifiche multiple
            // Es: ["2024-03-20 09:00:00", "2024-03-19 09:00:00"]
            // Permette di configurare più promemoria per la stessa scadenza
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
            // Es: "tutte le scadenze della prossima settimana"
            $table->index('data_ora');

            // Indice composito per filtrare per utente creatore e stato
            // Es: "tutte le scadenze completate create dall'utente X"
            $table->index(['user_id', 'stato']);

            // Indice composito per filtrare per utente assegnato e stato
            // Es: "tutte le scadenze da iniziare assegnate all'utente Y"
            $table->index(['assigned_to', 'stato']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scadenze');
    }
};
