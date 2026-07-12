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
        Schema::create('ratings_especialistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialista_id')->constrained('especialistas')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->unique(['paciente_id', 'cita_id']);
        });

        Schema::create('metricas_especialistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialista_id')->constrained('especialistas')->onDelete('cascade');
            $table->unsignedInteger('total_sesiones')->default(0);
            $table->unsignedInteger('sesiones_completadas')->default(0);
            $table->unsignedInteger('sesiones_canceladas')->default(0);
            $table->unsignedInteger('sesiones_puntuales')->default(0);
            $table->decimal('score_sistema', 3, 2)->default(0.00); // 0.00 a 5.00
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metricas_especialistas');
        Schema::dropIfExists('ratings_especialistas');
    }
};
