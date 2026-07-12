<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula cada cita con el servicio reservado (antes sólo existía el genérico
     * tipo_consulta = 'standard'). Permite reportes de rendimiento por servicio.
     *
     * Backfill: para las citas existentes se estima el servicio a partir del
     * especialista y la duración de la cita (usando el pivote especialista_servicio).
     */
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('servicio_id')->nullable()->after('especialista_id')
                ->constrained('servicios')->nullOnDelete();
        });

        // Mapa [especialista_id][duracion] => servicio_id (el primero que coincide).
        $pivot = DB::table('especialista_servicio as es')
            ->join('servicios as s', 's.id', '=', 'es.servicio_id')
            ->select('es.especialista_id', 's.duracion', 's.id as servicio_id')
            ->orderBy('s.id')
            ->get();

        $byEspDur = [];   // [esp][dur] => servicio_id
        $byEsp    = [];   // [esp] => primer servicio_id
        foreach ($pivot as $row) {
            $esp = (int) $row->especialista_id;
            $dur = (int) $row->duracion;
            $byEspDur[$esp][$dur] = $byEspDur[$esp][$dur] ?? (int) $row->servicio_id;
            $byEsp[$esp] = $byEsp[$esp] ?? (int) $row->servicio_id;
        }

        DB::table('citas')->whereNull('servicio_id')->orderBy('id')
            ->select('id', 'especialista_id', 'duracion_minutos')
            ->chunkById(200, function ($citas) use ($byEspDur, $byEsp) {
                foreach ($citas as $cita) {
                    $esp = (int) $cita->especialista_id;
                    $dur = (int) $cita->duracion_minutos;
                    $servicioId = $byEspDur[$esp][$dur] ?? $byEsp[$esp] ?? null;

                    if ($servicioId) {
                        DB::table('citas')->where('id', $cita->id)->update(['servicio_id' => $servicioId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropColumn('servicio_id');
        });
    }
};
