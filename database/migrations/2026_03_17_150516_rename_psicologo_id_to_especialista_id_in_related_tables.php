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
        Schema::table('citas', function (Blueprint $table) {
            $table->renameColumn('psicologo_id', 'especialista_id');
        });

        Schema::table('disponibilidades', function (Blueprint $table) {
            $table->renameColumn('psicologo_id', 'especialista_id');
        });

        Schema::table('horarios_semanales', function (Blueprint $table) {
            $table->renameColumn('psicologo_id', 'especialista_id');
        });

        Schema::table('nota_psicologos', function (Blueprint $table) {
            $table->renameColumn('psicologo_id', 'especialista_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nota_psicologos', function (Blueprint $table) {
            $table->renameColumn('especialista_id', 'psicologo_id');
        });

        Schema::table('horarios_semanales', function (Blueprint $table) {
            $table->renameColumn('especialista_id', 'psicologo_id');
        });

        Schema::table('disponibilidades', function (Blueprint $table) {
            $table->renameColumn('especialista_id', 'psicologo_id');
        });

        Schema::table('citas', function (Blueprint $table) {
            $table->renameColumn('especialista_id', 'psicologo_id');
        });
    }
};
