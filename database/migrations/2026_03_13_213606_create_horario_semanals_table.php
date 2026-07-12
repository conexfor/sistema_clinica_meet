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
        Schema::create('horarios_semanales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psicologo_id')->constrained('psicologos')->onDelete('cascade');
            $table->unsignedTinyInteger('dia_semana'); // 0 = Domingo, 1 = Lunes, etc.
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('esta_activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario_semanals');
    }
};
