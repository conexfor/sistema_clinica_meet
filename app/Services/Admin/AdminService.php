<?php

namespace App\Services\Admin;

use App\Models\Especialista;
use App\Models\Usuario;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminService
{
    public function __construct(
        private RevenueService $revenueService
    ) {}

    /**
     * Obtiene las estadísticas para el dashboard administrativo,
     * adaptadas exactamente a la estructura analítica premium requerida.
     */
    public function getDashboardStats(string $period): array
    {
        $now = now();
        $startDate = match ($period) {
            'today'      => $now->copy()->startOfDay(),
            'week'       => $now->copy()->startOfWeek(),
            'month'      => $now->copy()->startOfMonth(),
            'last_month' => $now->copy()->subMonth()->startOfMonth(),
            '6_months'   => $now->copy()->subMonths(6)->startOfMonth(),
            'year'       => $now->copy()->startOfYear(),
            default      => $now->copy()->startOfMonth(),
        };
        $endDate = match ($period) {
            'last_month' => $now->copy()->subMonth()->endOfMonth(),
            default      => $now->copy()->endOfDay(),
        };

        // For comparison (Growth)
        $diff = $startDate->diffInDays($endDate);
        $prevStartDate = $startDate->copy()->subDays($diff + 1);
        $prevEndDate = $endDate->copy()->subDays($diff + 1);

        // Estados que representan una cita reservada que genera ingreso.
        $revenueStates = [Cita::STATUS_RESERVADA, Cita::STATUS_ATTENTION, Cita::STATUS_COMPLETED];

        // 1. Base Query with Date Filter (sólo realizadas: para rating).
        $query = Cita::where('estado', Cita::STATUS_COMPLETED)
                     ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                     ->whereBetween('disponibilidades.fecha', [$startDate, $endDate]);

        // Query de ingresos: citas reservadas (no canceladas/reprogramadas).
        $revenueQuery = Cita::whereIn('estado', $revenueStates)
                     ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                     ->whereBetween('disponibilidades.fecha', [$startDate, $endDate]);

        // Ingresos: precio efectivo por cita (total real o estimado según el servicio).
        $citasRevenue = (clone $revenueQuery)->get(['citas.especialista_id', 'citas.servicio_id', 'citas.duracion_minutos', 'citas.total']);
        $totalRevenue = $this->revenueService->revenueForCitas($citasRevenue);

        // Prev Revenue for growth
        $citasPrev = Cita::whereIn('estado', $revenueStates)
             ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
             ->whereBetween('disponibilidades.fecha', [$prevStartDate, $prevEndDate])
             ->get(['citas.especialista_id', 'citas.servicio_id', 'citas.duracion_minutos', 'citas.total']);
        $prevRevenue = $this->revenueService->revenueForCitas($citasPrev);

        $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : ($totalRevenue > 0 ? 100 : 0);

        // Net Profit: ingresos efectivos menos los costos registrados.
        $totalCosto = (float) ((clone $revenueQuery)->sum('costo') ?? 0);
        $netProfit = $totalRevenue - $totalCosto;

        // No-Show Rate (Based on the period) — inasistencias reales (estado no_asistio),
        // consistente con el Panel de Control.
        $periodQuery = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                          ->whereBetween('disponibilidades.fecha', [$startDate, $endDate]);

        $totalCitas = (clone $periodQuery)->count();
        $noShows = (clone $periodQuery)->where('estado', Cita::STATUS_NO_SHOW)->count();
        $noShowRate = $totalCitas > 0 ? ($noShows / $totalCitas) * 100 : 0;
        
        // Pending Sessions (Global or future sessions)
        $pendingSessions = Cita::where('estado', Cita::STATUS_RESERVADA)->count();
        
        // Average Rating
        $avgRating = (clone $query)->where('calificacion', '>', 0)->avg('calificacion') ?? 0;
        
        // 2. Chart Data: Rendimiento por servicio real (ingresos con precio efectivo)
        $citasChart = (clone $revenueQuery)->get(['citas.especialista_id', 'citas.servicio_id', 'citas.duracion_minutos', 'citas.total']);
        $serviciosNombre = \App\Models\Servicio::pluck('nombre', 'id');

        $porServicio = $citasChart->groupBy(fn ($c) => $serviciosNombre[$c->servicio_id] ?? 'Sin servicio');

        $serviceRevenue = $porServicio
            ->map(fn ($grupo) => round($this->revenueService->revenueForCitas($grupo), 2))
            ->toArray();
        $serviceVolume = $porServicio
            ->map(fn ($grupo) => $grupo->count())
            ->toArray();

        if (empty($serviceRevenue)) {
            $serviceRevenue = ['Sin Datos' => 0];
            $serviceVolume = ['Sin Datos' => 0];
        }

        // 3. Recent Appointments Table
        $ultimasCitas = Cita::with(['paciente', 'especialista.usuario', 'disponibilidad', 'modalidad'])
                            ->latest()
                            ->take(8)
                            ->get();

        return [
            'current_period' => $period,
            'kpis' => [
                'total_revenue'    => $totalRevenue,
                'revenue_growth'   => number_format($revenueGrowth, 1), 
                'net_profit'       => $netProfit,
                'profit_margin'    => $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0,
                'no_show_rate'     => number_format($noShowRate, 1),
                'no_show_improve'  => 0,
                'pending_sessions' => $pendingSessions,
                'avg_rating'       => number_format($avgRating, 1),
                'vat_due'          => $totalRevenue * 0.18, 
            ],
            'charts' => [
                'revenue_labels' => array_keys($serviceRevenue),
                'revenue_values' => array_values($serviceRevenue),
                'volume_labels'  => array_keys($serviceVolume),
                'volume_values'  => array_values($serviceVolume),
            ],
            'ultimas_citas'     => $ultimasCitas,
            'total_especialistas' => Especialista::count(),
            'total_pacientes'     => Usuario::whereHas('rol', function ($q) { $q->where('nombre', 'patient'); })->count(),
        ];
    }
}
