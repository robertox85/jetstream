<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
       INSERT INTO evento_invitati (evento_id, user_id, status, created_at, updated_at)
       SELECT id, assigned_to, 'pending', NOW(), NOW() 
       FROM eventi 
       WHERE assigned_to IS NOT NULL
   ");

        DB::statement("
        UPDATE eventi
        SET assigned_to = NULL
        WHERE assigned_to IS NOT NULL
        "
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
        DELETE FROM evento_invitati
        WHERE EXISTS (
            SELECT 1
            FROM eventi
            WHERE eventi.id = evento_invitati.evento_id
            AND eventi.assigned_to IS NOT NULL
            "
        );
    }
};