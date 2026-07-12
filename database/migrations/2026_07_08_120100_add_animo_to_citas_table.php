<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado de ánimo registrado en la sesión: al inicio y al final (escala 1-5).
     */
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->unsignedTinyInteger('animo_inicial')->nullable()->after('resumen_compartido');
            $table->unsignedTinyInteger('animo_final')->nullable()->after('animo_inicial');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['animo_inicial', 'animo_final']);
        });
    }
};
