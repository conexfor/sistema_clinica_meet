<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite ligar notas del especialista a un paciente concreto y marcarlas
     * como notas de crisis (para el flujo de "Reportar crisis").
     */
    public function up(): void
    {
        Schema::table('notas_especialistas', function (Blueprint $table) {
            $table->foreignId('paciente_id')->nullable()->after('especialista_id')->constrained('usuarios')->nullOnDelete();
            $table->string('tipo')->default('general')->after('contenido'); // general | crisis
        });
    }

    public function down(): void
    {
        Schema::table('notas_especialistas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paciente_id');
            $table->dropColumn('tipo');
        });
    }
};
