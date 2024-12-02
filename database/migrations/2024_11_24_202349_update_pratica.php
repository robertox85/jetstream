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
        // aggiungi una tabella pratiche_utenti con id_pratica e id_utente
        Schema::create('pratiche_utenti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pratica_id')->constrained('pratiche');
            $table->foreignId('user_id')->constrained('users');
            $table->string('permission_type')->default('read');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pratiche_utenti');
    }
};
