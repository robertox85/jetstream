<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('eventi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pratica_id')->constrained('pratiche')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->enum('tipo', ['scadenza', 'udienza', 'appuntamento']);
            $table->dateTime('data_ora');
            $table->string('motivo');
            $table->string('luogo')->nullable();
            $table->enum('stato', ['da_iniziare', 'in_corso', 'completata', 'annullata'])->default('da_iniziare');
            $table->json('reminder_at')->nullable();
            $table->boolean('email_notification')->default(true);
            $table->boolean('browser_notification')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to', 'stato']);
            $table->index('data_ora');
            $table->index(['user_id', 'stato']);
            $table->index('tipo');
        });

        // Migrazione dati dalle vecchie tabelle
        $this->migrateData();
    }

    private function migrateData()
    {
        // Migrazione scadenze
        DB::table('scadenze')->orderBy('id')->each(function ($scadenza) {
            DB::table('eventi')->insert([
                'pratica_id' => $scadenza->pratica_id,
                'user_id' => $scadenza->user_id,
                'assigned_to' => $scadenza->assigned_to,
                'tipo' => 'scadenza',
                'data_ora' => $scadenza->data_ora,
                'motivo' => $scadenza->motivo,
                'luogo' => null,
                'stato' => $scadenza->stato,
                'reminder_at' => $scadenza->reminder_at,
                'email_notification' => $scadenza->email_notification,
                'browser_notification' => $scadenza->browser_notification,
                'created_at' => $scadenza->created_at,
                'updated_at' => $scadenza->updated_at,
                'deleted_at' => $scadenza->deleted_at,
            ]);
        });

        // Migrazione udienze
        DB::table('udienze')->orderBy('id')->each(function ($udienza) {
            DB::table('eventi')->insert([
                'pratica_id' => $udienza->pratica_id,
                'user_id' => $udienza->user_id,
                'assigned_to' => $udienza->assigned_to,
                'tipo' => 'udienza',
                'data_ora' => $udienza->data_ora,
                'motivo' => $udienza->motivo,
                'luogo' => $udienza->luogo,
                'stato' => $udienza->stato,
                'reminder_at' => $udienza->reminder_at,
                'email_notification' => $udienza->email_notification,
                'browser_notification' => $udienza->browser_notification,
                'created_at' => $udienza->created_at,
                'updated_at' => $udienza->updated_at,
                'deleted_at' => $udienza->deleted_at,
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('eventi');
    }
};