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
        // if not exists
        if (Schema::hasTable('categorie_documenti')) {
            return;
        }

        Schema::create('categorie_documenti', function (Blueprint $table) {
            // Chiave primaria autoincrement
            $table->id();

            // Nome della categoria
            // Es: "Atti di causa", "Documenti cliente", "Fatture"
            $table->string('nome');

            // Descrizione opzionale della categoria
            $table->string('descrizione')->nullable();

            // created_at e updated_at automatici
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorie_documenti');
    }
};
