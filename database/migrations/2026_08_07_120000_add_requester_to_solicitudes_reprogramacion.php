<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add requester info to solicitudes_reprogramacion so we can track
     * who initiated the request (paciente, especialista, recepcionista)
     * and optionally which user performed the action.
     */
    public function up(): void
    {
        Schema::table('solicitudes_reprogramacion', function (Blueprint $table) {
            $table->string('solicitante')->nullable()->after('motivo'); // paciente|especialista|recepcionista
            $table->foreignId('solicitante_usuario_id')->nullable()->after('solicitante')->constrained('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_reprogramacion', function (Blueprint $table) {
            $table->dropForeign(['solicitante_usuario_id']);
            $table->dropColumn(['solicitante_usuario_id', 'solicitante']);
        });
    }
};
