<?php

namespace App\Services\Admin;

use App\Models\Cita;
use App\Models\Modalidad;
use Illuminate\Support\Facades\DB;

/**
 * FinanceReportService
 * Métricas financieras y de actividad del admin: ingresos brutos con crecimiento,
 * pacientes activos, tasa de retención, sesiones realizadas e ingreso por modalidad.
 * Todo deriva de `citas` (estado realizada) unida a `disponibilidades` por fecha.
 */
class FinanceReportService
{
    public function __construct(
        private RevenueService $revenueService
    ) {}

    /**
     * Estados que representan una cita reservada que genera ingreso
     * (no canceladas ni reprogramadas ni inasistencias).
     */
    private const REVENUE_STATES = [
        Cita::STATUS_RESERVADA,
        Cita::STATUS_ATTENTION,
        Cita::STATUS_COMPLETED,
    ];

    /**
     * Query base de citas realizadas dentro de un rango de fechas de sesión.
     */
    private function realizadasEntre($start, $end)
    {
        return Cita::where('citas.estado', Cita::STATUS_COMPLETED)
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$start, $end]);
    }

    /**
     * Query base de citas reservadas (con ingreso) dentro de un rango de fechas.
     */
    private function reservadasEntre($start, $end)
    {
        return Cita::whereIn('citas.estado', self::REVENUE_STATES)
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$start, $end]);
    }

    /**
     * Ingreso efectivo del período usando precios de servicio (o el total real si existe).
     */
    private function revenueEntre($start, $end): float
    {
        $citas = $this->reservadasEntre($start, $end)
            ->get(['citas.especialista_id', 'citas.servicio_id', 'citas.duracion_minutos', 'citas.total']);

        return $this->revenueService->revenueForCitas($citas);
    }

    public function getStats(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $p = ReportPeriod::make($period, $startDate, $endDate);

        // --- Ingresos brutos y crecimiento vs período anterior ---
        $revenue     = $this->revenueEntre($p->start, $p->end);
        $prevRevenue = $this->revenueEntre($p->prevStart, $p->prevEnd);
        $revenueGrowth = $prevRevenue > 0
            ? (($revenue - $prevRevenue) / $prevRevenue) * 100
            : ($revenue > 0 ? 100 : 0);

        // --- Sesiones realizadas en el período ---
        $sessions     = (clone $this->realizadasEntre($p->start, $p->end))->sum('citas.cantidad_sesiones');
        $prevSessions = (clone $this->realizadasEntre($p->prevStart, $p->prevEnd))->sum('citas.cantidad_sesiones');
        $sessionsGrowth = $prevSessions > 0
            ? (($sessions - $prevSessions) / $prevSessions) * 100
            : ($sessions > 0 ? 100 : 0);

        // --- Pacientes activos: ≥1 sesión (realizada/confirmada) en los últimos 30 días ---
        $activePatients = Cita::whereIn('citas.estado', [Cita::STATUS_COMPLETED, Cita::STATUS_RESERVADA])
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('disponibilidades.fecha', '>=', now()->subDays(30)->startOfDay())
            ->distinct('citas.paciente_id')
            ->count('citas.paciente_id');

        // --- Tasa de retención: pacientes con ≥2 sesiones realizadas / pacientes con ≥1 ---
        $sesionesPorPaciente = Cita::where('estado', Cita::STATUS_COMPLETED)
            ->select('paciente_id', DB::raw('SUM(cantidad_sesiones) as total'))
            ->groupBy('paciente_id')
            ->pluck('total');
        $conPrimera  = $sesionesPorPaciente->count();
        $recurrentes = $sesionesPorPaciente->filter(fn ($t) => $t >= 2)->count();
        $retentionRate = $conPrimera > 0 ? ($recurrentes / $conPrimera) * 100 : 0;

        // --- Ingreso por modalidad (Virtual vs Presencial) en el período ---
        $citasModal = $this->reservadasEntre($p->start, $p->end)
            ->get(['citas.especialista_id', 'citas.servicio_id', 'citas.duracion_minutos', 'citas.total', 'citas.modalidad_id']);

        $modalidadNombres = Modalidad::pluck('nombre', 'id');
        $modalidades = $citasModal->groupBy('modalidad_id')->map(fn ($grupo, $modalidadId) => [
            'nombre'  => $modalidadNombres[$modalidadId] ?? 'Sin modalidad',
            'revenue' => $this->revenueService->revenueForCitas($grupo),
            'count'   => $grupo->sum('cantidad_sesiones'),
        ])->values();

        if ($modalidades->isEmpty()) {
            $modalidades = collect([['nombre' => 'Sin datos', 'revenue' => 0, 'count' => 0]]);
        }

        return [
            'period'          => $p->key,
            'period_label'    => $p->label,
            'revenue'         => $revenue,
            'revenue_growth'  => round($revenueGrowth, 1),
            'prev_revenue'    => $prevRevenue,
            'sessions'        => $sessions,
            'sessions_growth' => round($sessionsGrowth, 1),
            'active_patients' => $activePatients,
            'retention_rate'  => round($retentionRate, 1),
            'recurrentes'     => $recurrentes,
            'con_primera'     => $conPrimera,
            'modalidades'     => $modalidades,
        ];
    }
}
