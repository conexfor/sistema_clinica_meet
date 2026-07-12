<?php

namespace App\Services\Admin;

use App\Models\Cita;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * RevenueService
 * Calcula el ingreso "efectivo" de una cita:
 *   - Si la cita tiene un total registrado (> 0), se usa ese valor exacto.
 *   - Si no (p. ej. datos sin monto), se estima con el precio del servicio que
 *     ofrece ese especialista y coincide en duración, con fallbacks razonables.
 *
 * Así los ingresos reflejan los precios de los servicios por cada cita reservada,
 * sin depender de que la columna `total` esté cargada.
 */
class RevenueService
{
    /** @var array<int,float>|null  [servicio_id] => precio */
    private ?array $byServicio = null;
    /** @var array<int,array<int,float>>|null  [especialista_id][duracion] => precio promedio */
    private ?array $byEspDur = null;
    /** @var array<int,float>|null  [especialista_id] => precio promedio de sus servicios */
    private ?array $byEsp = null;
    /** Precio promedio global (último recurso). */
    private float $globalAvg = 0.0;

    /**
     * Precio efectivo de una cita:
     *   1) total real si está registrado (> 0),
     *   2) precio del servicio reservado (servicio_id),
     *   3) estimación por especialista + duración,
     *   4) promedio global.
     * La cita debe traer: total, servicio_id, especialista_id, duracion_minutos.
     */
    public function effectivePrice(Cita $cita): float
    {
        if ((float) $cita->total > 0) {
            return (float) $cita->total;
        }

        $this->ensureMap();

        if ($cita->servicio_id && isset($this->byServicio[(int) $cita->servicio_id])) {
            return $this->byServicio[(int) $cita->servicio_id];
        }

        $esp = (int) $cita->especialista_id;
        $dur = (int) $cita->duracion_minutos;

        return $this->byEspDur[$esp][$dur]
            ?? $this->byEsp[$esp]
            ?? $this->globalAvg;
    }

    /**
     * Suma el ingreso efectivo de una colección de citas.
     */
    public function revenueForCitas(Collection $citas): float
    {
        return (float) $citas->reduce(fn ($carry, $c) => $carry + $this->effectivePrice($c), 0.0);
    }

    /**
     * Construye el mapa de precios por especialista+duración a partir del pivote
     * especialista_servicio y los precios de los servicios. Se cachea por request.
     */
    private function ensureMap(): void
    {
        if ($this->byEspDur !== null) {
            return;
        }

        $this->byEspDur = [];
        $this->byEsp = [];
        $this->byServicio = DB::table('servicios')->pluck('precio', 'id')
            ->map(fn ($p) => (float) $p)->toArray();

        $rows = DB::table('especialista_servicio as es')
            ->join('servicios as s', 's.id', '=', 'es.servicio_id')
            ->select('es.especialista_id', 's.duracion', 's.precio')
            ->get();

        $tmpEspDur = [];
        $tmpEsp = [];
        foreach ($rows as $r) {
            $esp = (int) $r->especialista_id;
            $dur = (int) $r->duracion;
            $precio = (float) $r->precio;
            $tmpEspDur[$esp][$dur][] = $precio;
            $tmpEsp[$esp][] = $precio;
        }

        foreach ($tmpEspDur as $esp => $durs) {
            foreach ($durs as $dur => $precios) {
                $this->byEspDur[$esp][$dur] = array_sum($precios) / count($precios);
            }
        }
        foreach ($tmpEsp as $esp => $precios) {
            $this->byEsp[$esp] = array_sum($precios) / count($precios);
        }

        $this->globalAvg = (float) (DB::table('servicios')->avg('precio') ?? 0);
    }
}
