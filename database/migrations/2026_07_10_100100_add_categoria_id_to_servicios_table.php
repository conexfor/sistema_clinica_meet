<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la relación categoria_id a servicios y migra el campo string
     * existente ("categoria") a filas gestionables en la tabla categorias.
     * Se conserva la columna string por compatibilidad hacia atrás.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->foreignId('categoria_id')->nullable()->after('categoria')
                ->constrained('categorias')->nullOnDelete();
        });

        // Backfill: por cada valor distinto de servicios.categoria, crear/emparejar
        // una fila en categorias y asignar el categoria_id correspondiente.
        $categoriasExistentes = DB::table('servicios')
            ->whereNotNull('categoria')
            ->where('categoria', '<>', '')
            ->distinct()
            ->pluck('categoria');

        foreach ($categoriasExistentes as $nombre) {
            $categoriaId = DB::table('categorias')->where('nombre', $nombre)->value('id');

            if (! $categoriaId) {
                $categoriaId = DB::table('categorias')->insertGetId([
                    'nombre'      => $nombre,
                    'esta_activo' => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            DB::table('servicios')->where('categoria', $nombre)->update([
                'categoria_id' => $categoriaId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });
    }
};
