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
        Schema::table('eventi', function (Blueprint $table) {
            $table->dropForeign(['pratica_id']);  // rimuove il vincolo
            $table->foreignId('pratica_id')->nullable()->change();  // rende la colonna nullable
            // Aggiunti google_event_id e google_event_link
            $table->string('google_event_id')->nullable();
            $table->string('google_event_link')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventi', function (Blueprint $table) {
            $table->foreignId('pratica_id')->nullable(false)->change();  // torna non nullable
            $table->foreign('pratica_id')
                ->references('id')
                ->on('pratiche')
                ->onDelete('cascade');  // ricrea il vincolo

            $table->dropColumn('google_event_id');
            $table->dropColumn('google_event_link');
        });
    }
};
