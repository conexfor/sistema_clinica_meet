<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tareas que el especialista asigna a un paciente durante/tras una sesión.
     */
    public function up(): void
    {
        Schema::create('tareas_paciente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialista_id')->constrained('especialistas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->boolean('completada')->default(false);
            $table->timestamp('completada_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas_paciente');
    }
};
