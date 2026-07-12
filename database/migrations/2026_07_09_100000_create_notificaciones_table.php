<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notificaciones en la aplicación (campanita) para los usuarios.
     */
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('tipo')->default('general'); // recordatorio | reprogramacion | tarea | cancelacion | general
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('url')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->index(['usuario_id', 'leida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
