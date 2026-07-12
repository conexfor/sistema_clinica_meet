<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nota: estas columnas ya se crean en la migración v2
     * (2026_03_20_101830). Esta migración se conserva por el historial,
     * pero se añaden guardas para que sea idempotente y no falle en un
     * `migrate:fresh` con "Duplicate column".
     */
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (!Schema::hasColumn('citas', 'atendido_at')) {
                $table->timestamp('atendido_at')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('citas', 'finalizado_at')) {
                $table->timestamp('finalizado_at')->nullable()->after('atendido_at');
            }
            if (!Schema::hasColumn('citas', 'cancelado_por_especialista')) {
                $table->boolean('cancelado_por_especialista')->default(false)->after('motivo_cancelacion');
            }
        });

        Schema::table('especialistas', function (Blueprint $table) {
            if (!Schema::hasColumn('especialistas', 'rating_promedio')) {
                $table->decimal('rating_promedio', 3, 2)->default(0.00)->after('foto');
            }
            if (!Schema::hasColumn('especialistas', 'rating_final')) {
                $table->decimal('rating_final', 3, 2)->default(0.00)->after('rating_promedio');
            }
            if (!Schema::hasColumn('especialistas', 'total_reviews')) {
                $table->unsignedInteger('total_reviews')->default(0)->after('rating_final');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * No revertimos aquí para no eliminar columnas que "posee" la v2.
     */
    public function down(): void
    {
        // Intencionalmente vacío: las columnas las gestiona la migración v2.
    }
};
