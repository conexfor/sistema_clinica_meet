<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Resumen de la sesión que el psicólogo puede autorizar a compartir con el paciente.
     */
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->text('resumen_sesion')->nullable()->after('comentario_paciente');
            $table->boolean('resumen_compartido')->default(false)->after('resumen_sesion');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['resumen_sesion', 'resumen_compartido']);
        });
    }
};
