<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tickets de soporte: incidencias reportadas por pacientes o doctores
     * (ej. "No puedo entrar al meet", "No me aparece mi cita").
     */
    public function up(): void
    {
        Schema::create('tickets_soporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('rol')->nullable();          // patient | doctor | ...
            $table->string('categoria')->default('otro'); // meet | cita | pago | cuenta | otro
            $table->string('asunto');
            $table->text('mensaje');
            $table->string('estado')->default('abierto'); // abierto | en_proceso | resuelto
            $table->text('respuesta')->nullable();
            $table->foreignId('respondido_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('respondido_at')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets_soporte');
    }
};
