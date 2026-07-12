<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log de auditoría: registro de acciones sensibles (inicios de sesión,
     * cambios de precios, bajas de pacientes, etc.) para seguridad del admin.
     */
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('accion');                 // login | servicio.precio | paciente.baja | ...
            $table->string('categoria')->default('general'); // auth | finanzas | pacientes | sistema
            $table->text('descripcion');
            $table->string('ip', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['categoria', 'created_at']);
            $table->index('accion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
