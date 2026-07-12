<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Solicitudes de reprogramación creadas por el paciente y aprobadas por el especialista.
     */
    public function up(): void
    {
        Schema::create('solicitudes_reprogramacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('especialista_id')->constrained('especialistas')->onDelete('cascade');
            $table->string('slot_id'); // ID dinámico de disponibilidad elegido por el paciente
            $table->date('fecha_solicitada');
            $table->time('hora_solicitada');
            $table->text('motivo')->nullable();
            $table->string('estado')->default('pendiente'); // pendiente | aprobada | rechazada
            $table->text('respuesta_especialista')->nullable();
            $table->timestamp('respondida_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_reprogramacion');
    }
};
