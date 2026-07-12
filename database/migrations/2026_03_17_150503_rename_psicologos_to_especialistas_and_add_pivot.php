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
        // Renombrar la tabla base si existe
        if (Schema::hasTable('psicologos') && !Schema::hasTable('especialistas')) {
            Schema::rename('psicologos', 'especialistas');
        }

        // Modificar campos en especialistas
        Schema::table('especialistas', function (Blueprint $table) {
            if (Schema::hasColumn('especialistas', 'foto_perfil')) {
                $table->renameColumn('foto_perfil', 'foto');
            }
        });

        // Crear tabla pivote para la relación muchos a muchos
        Schema::create('especialista_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialista_id')->constrained('especialistas')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialista_servicio');
        
        if (Schema::hasTable('especialistas')) {
            Schema::table('especialistas', function (Blueprint $table) {
                if (Schema::hasColumn('especialistas', 'foto')) {
                    $table->renameColumn('foto', 'foto_perfil');
                }
            });

            if (!Schema::hasTable('psicologos')) {
                Schema::rename('especialistas', 'psicologos');
            }
        }
    }
};
