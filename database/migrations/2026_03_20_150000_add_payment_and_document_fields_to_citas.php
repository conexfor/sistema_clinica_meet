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
        // 1. Agregar campos de pago y facturación a la tabla citas
        Schema::table('citas', function (Blueprint $table) {
            $table->string('metodo_pago')->nullable()->after('total'); // efectivo, tarjeta, transferencia, yape, plin
            $table->string('tipo_comprobante')->nullable()->after('metodo_pago'); // boleta, factura, nota_venta
            $table->string('documento_serie')->nullable()->after('tipo_comprobante');
            $table->integer('documento_correlativo')->nullable()->after('documento_serie');
        });

        // 2. Crear tabla para la configuración de correlativos
        Schema::create('configuracion_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->unique(); // boleta, factura, nota_venta
            $table->string('serie');
            $table->integer('ultimo_correlativo')->default(0);
            $table->timestamps();
        });

        // 3. Poblar configuración inicial
        DB::table('configuracion_comprobantes')->insert([
            ['tipo' => 'boleta', 'serie' => 'B001', 'ultimo_correlativo' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'factura', 'serie' => 'F001', 'ultimo_correlativo' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['tipo' => 'nota_venta', 'serie' => 'NV01', 'ultimo_correlativo' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'tipo_comprobante', 'documento_serie', 'documento_correlativo']);
        });
        Schema::dropIfExists('configuracion_comprobantes');
    }
};
