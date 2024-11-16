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
        // Tabella ponte per la relazione many-to-many tra anagrafiche e pratiche
        Schema::create('anagrafica_pratica', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Relazione con la pratica
            // restrict: impedisce l'eliminazione della pratica se esistono relazioni
            $table->foreignId('pratica_id')
                ->constrained('pratiche')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            // Relazione con l'anagrafica
            // restrict: impedisce l'eliminazione dell'anagrafica se esistono relazioni
            $table->foreignId('anagrafica_id')
                ->constrained('anagrafiche')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            // Tipo di relazione con la pratica
            // 'controparte': parte avversa
            // 'assistito': cliente
            $table->string('tipo_relazione')->default('controparte');

            // created_at e updated_at automatici
            $table->timestamps();

            // Soft delete - non elimina fisicamente il record ma lo marca come eliminato
            $table->softDeletes();

            // Vincolo di unicità: impedisce duplicati nella relazione
            // Una stessa anagrafica non può essere più volte assistito o controparte nella stessa pratica
            $table->unique(
                ['pratica_id', 'anagrafica_id', 'tipo_relazione'],
                'unique_relation'
            );
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anagrafica_pratica');
    }
};
