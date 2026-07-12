<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('configuracion_comprobantes')->insert([
            ['tipo' => 'otros', 'serie' => 'O001', 'ultimo_correlativo' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'ticket', 'serie' => 'T001', 'ultimo_correlativo' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'nota_debito', 'serie' => 'ND01', 'ultimo_correlativo' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'nota_credito', 'serie' => 'NC01', 'ultimo_correlativo' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'guia_remision', 'serie' => 'G001', 'ultimo_correlativo' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configuracion_comprobantes')
            ->whereIn('tipo', ['otros', 'ticket', 'nota_debito', 'nota_credito', 'guia_remision'])
            ->delete();
    }
};
