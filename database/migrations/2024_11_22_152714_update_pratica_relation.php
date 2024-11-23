<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {


        // Drop Contabilita e Lavorationi per ora
        Schema::dropIfExists('contabilita');
        Schema::dropIfExists('lavorazioni');


        // Elimino il vincolo con gli utenti
        Schema::table('scadenze', function (Blueprint $table) {
            // $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change(); // Rendi nullable
        });
        Schema::table('udienze', function (Blueprint $table) {
            // $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change(); // Rendi nullable
        });
        Schema::table('note', function (Blueprint $table) {
            // $table->dropForeign(['user_id']);
            // $table->dropForeign(['last_edited_by']);
            $table->unsignedBigInteger('user_id')->nullable()->change(); // Rendi nullable
            $table->unsignedBigInteger('last_edited_by')->nullable()->change(); // Rendi nullable
        });


        // Evito le eliminazioni in cascata
        Schema::table('scadenze', function (Blueprint $table) {
            $table->unsignedBigInteger('pratica_id')->nullable()->change(); // Rendi nullable
            // $table->dropForeign(['pratica_id']);
            //$table->foreign('pratica_id')
            //    ->references('id')->on('pratiche')
            //    ->nullOnDelete(); // Per evitare eliminazioni fisiche
        });

        Schema::table('udienze', function (Blueprint $table) {
            $table->unsignedBigInteger('pratica_id')->nullable()->change(); // Rendi nullable
           //$table->dropForeign(['pratica_id']);
           //$table->foreign('pratica_id')
           //    ->references('id')->on('pratiche')
           //    ->nullOnDelete(); // Per evitare eliminazioni fisiche
        });

        Schema::table('note', function (Blueprint $table) {
            $table->unsignedBigInteger('pratica_id')->nullable()->change(); // Rendi nullable
            //$table->dropForeign(['pratica_id']);
            //$table->foreign('pratica_id')
            //    ->references('id')->on('pratiche')
            //    ->nullOnDelete(); // Per evitare eliminazioni fisiche
        });

    }

    public function down(): void {
        // Non posso tornare indietro
    }
};
