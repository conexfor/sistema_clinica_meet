<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de recordatorios de cita enviados (log de notificaciones automáticas).
     */
    public function up(): void
    {
        Schema::create('recordatorios_cita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('especialista_id')->constrained('especialistas')->onDelete('cascade');
            $table->date('fecha_cita');
            $table->string('canal')->default('email'); // email | log
            $table->timestamp('enviado_at')->nullable();
            $table->timestamps();

            // Un solo recordatorio por cita
            $table->unique('cita_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios_cita');
    }
};
