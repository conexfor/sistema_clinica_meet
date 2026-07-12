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
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'numero')) {
                $table->dropColumn('numero');
            }
            if (!Schema::hasColumn('usuarios', 'telefono')) {
                $table->string('telefono')->nullable()->after('email');
            }
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('pacientes', 'numero')) $colsToDrop[] = 'numero';
            if (Schema::hasColumn('pacientes', 'telefono')) $colsToDrop[] = 'telefono';
            
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            if (!Schema::hasColumn('pacientes', 'telefono')) $table->string('telefono')->nullable();
            if (!Schema::hasColumn('pacientes', 'numero')) $table->string('numero')->nullable();
        });

        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'telefono')) $table->dropColumn('telefono');
            if (!Schema::hasColumn('usuarios', 'numero')) $table->string('numero')->nullable();
        });
    }
};
