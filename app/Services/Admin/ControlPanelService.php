<?php

namespace App\Services\Admin;

use App\Models\Auditoria;
use App\Models\Cita;
use App\Models\TicketSoporte;

/**
 * ControlPanelService
 * Panel de control operativo: tasa de ausentismo (inasistencias reales, estado
 * no_asistio) y detección de picos inusuales de cancelaciones.
 */
class ControlPanelService
{
    /** Umbral: cancelaciones de la última semana vs. promedio previo. */
    private const SPIKE_FACTOR = 1.5;
    private const SPIKE_MIN    = 3;

    public function getStats(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $p = ReportPeriod::make($period, $startDate, $endDate);

        // --- Ausentismo del período (inasistencias reales: estado no_asistio) ---
        $totalCitas = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$p->start, $p->end])
            ->sum('citas.cantidad_sesiones');

        $noShows = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$p->start, $p->end])
            ->where('citas.estado', Cita::STATUS_NO_SHOW)
            ->sum('citas.cantidad_sesiones');

        $noShowRate = $totalCitas > 0 ? ($noShows / $totalCitas) * 100 : 0;

        // --- Pico de cancelaciones: últimos 7 días vs. promedio de las 3 semanas previas ---
        $last7 = Cita::where('estado', Cita::STATUS_CANCELLED)
            ->whereBetween('updated_at', [now()->subDays(7), now()])
            ->count();

        $prev21 = Cita::where('estado', Cita::STATUS_CANCELLED)
            ->whereBetween('updated_at', [now()->subDays(28), now()->subDays(7)])
            ->count();
        $prevWeeklyAvg = $prev21 / 3;

        $spike = $last7 >= self::SPIKE_MIN
            && $prevWeeklyAvg > 0
            && $last7 > $prevWeeklyAvg * self::SPIKE_FACTOR;

        return [
            'period'          => $p->key,
            'period_label'    => $p->label,
            'total_citas'     => $totalCitas,
            'no_show_count'   => $noShows,
            'no_show_rate'    => round($noShowRate, 1),
            'cancel_last7'    => $last7,
            'cancel_prev_avg' => round($prevWeeklyAvg, 1),
            'cancel_spike'    => $spike,
            'audit_recent'    => Auditoria::with('usuario')->latest()->limit(6)->get(),
            'soporte_abiertos' => TicketSoporte::whereIn('estado', [TicketSoporte::ESTADO_ABIERTO, TicketSoporte::ESTADO_EN_PROCESO])->count(),
            'soporte_recent'   => TicketSoporte::with('usuario')->latest()->limit(5)->get(),
        ];
    }
}
