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
        Schema::rename('nota_psicologos', 'notas_especialistas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('notas_especialistas', 'nota_psicologos');
    }
};
