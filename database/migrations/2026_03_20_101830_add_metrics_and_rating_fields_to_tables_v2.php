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
            $table->timestamp('atendido_at')->nullable()->after('estado');
            $table->timestamp('finalizado_at')->nullable()->after('atendido_at');
            $table->boolean('cancelado_por_especialista')->default(false)->after('motivo_cancelacion');
        });

        Schema::table('especialistas', function (Blueprint $table) {
            $table->decimal('rating_promedio', 3, 2)->default(0.00)->after('foto');
            $table->decimal('rating_final', 3, 2)->default(0.00)->after('rating_promedio');
            $table->unsignedInteger('total_reviews')->default(0)->after('rating_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('especialistas', function (Blueprint $table) {
            $table->dropColumn(['rating_promedio', 'rating_final', 'total_reviews']);
        });

        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn(['atendido_at', 'finalizado_at', 'cancelado_por_especialista']);
        });
    }
};
