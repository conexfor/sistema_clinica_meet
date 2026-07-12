<?php

namespace App\Services\Admin;

use App\Models\Cita;
use App\Models\Disponibilidad;
use App\Models\ListaEspera;
use App\Models\Modalidad;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * OccupancyReportService
 * Logística y demanda: mapa de calor (días × horas) de horarios reservados y
 * comparativo de citas Presencial vs Virtual.
 */
class OccupancyReportService
{
    /** Rango horario mostrado en el mapa de calor. */
    private const HOUR_START = 7;
    private const HOUR_END   = 21;

    private const DAY_LABELS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    public function getStats(string $period): array
    {
        $p = ReportPeriod::make($period);
        $hours = range(self::HOUR_START, self::HOUR_END);

        // Matriz 7 (días) × N (horas) inicializada en 0.
        $matrix = [];
        foreach (array_keys(self::DAY_LABELS) as $d) {
            $matrix[$d] = array_fill(0, count($hours), 0);
        }

        // Slots reservados dentro del período.
        $slots = Disponibilidad::where('esta_reservada', true)
            ->whereBetween('fecha', [$p->start, $p->end])
            ->get(['fecha', 'hora_inicio']);

        $max = 0;
        foreach ($slots as $slot) {
            $fecha = $slot->fecha instanceof Carbon ? $slot->fecha : Carbon::parse($slot->fecha);
            $dayIdx = $fecha->dayOfWeekIso - 1; // 0 = Lunes ... 6 = Domingo
            $hour = (int) substr((string) $slot->hora_inicio, 0, 2);
            $hourIdx = array_search($hour, $hours, true);

            if ($hourIdx !== false && isset($matrix[$dayIdx])) {
                $matrix[$dayIdx][$hourIdx]++;
                $max = max($max, $matrix[$dayIdx][$hourIdx]);
            }
        }

        // Presencial vs Virtual: conteo de citas por modalidad en el período.
        $porModalidad = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$p->start, $p->end])
            ->select('citas.modalidad_id', DB::raw('COUNT(*) as count'))
            ->groupBy('citas.modalidad_id')
            ->pluck('count', 'modalidad_id');

        $modalidadNombres = Modalidad::pluck('nombre', 'id');
        $totalModal = $porModalidad->sum();
        $modalidadBars = $modalidadNombres->map(fn ($nombre, $id) => [
            'nombre' => $nombre,
            'count'  => (int) ($porModalidad[$id] ?? 0),
            'pct'    => $totalModal > 0 ? round((($porModalidad[$id] ?? 0) / $totalModal) * 100, 1) : 0,
        ])->values();

        return [
            'period'       => $p->key,
            'period_label' => $p->label,
            'days'         => self::DAY_LABELS,
            'hours'        => $hours,
            'matrix'       => $matrix,
            'max'          => $max,
            'total_slots'  => $slots->count(),
            'modalidad_bars' => $modalidadBars,
            'waitlist_pendientes' => ListaEspera::pendientes()->count(),
            'waitlist_total'      => ListaEspera::count(),
        ];
    }
}
