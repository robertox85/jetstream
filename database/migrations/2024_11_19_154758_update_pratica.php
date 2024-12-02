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
        // Add Contabilita field to Pratica, and Lavorazione field to Pratica. Is a varchar field.
        Schema::table('pratiche', function (Blueprint $table) {
            $table->longText('contabilita')->nullable();
            $table->longText('lavorazione')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pratiche', function (Blueprint $table) {
            $table->dropColumn('contabilita');
            $table->dropColumn('lavorazione');
        });
    }
};
