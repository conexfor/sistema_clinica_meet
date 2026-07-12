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
            $table->text('motivo_cancelacion')->nullable()->after('estado');
            $table->foreignId('cita_original_id')->nullable()->after('motivo_cancelacion')->constrained('citas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['cita_original_id']);
            $table->dropColumn(['motivo_cancelacion', 'cita_original_id']);
        });
    }
};
