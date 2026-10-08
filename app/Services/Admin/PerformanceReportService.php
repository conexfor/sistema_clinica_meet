<?php

namespace App\Services\Admin;

use App\Models\Cita;
use App\Models\Especialista;
use Illuminate\Support\Facades\DB;

/**
 * PerformanceReportService
 * Desempeño de los especialistas (RRHH): ranking de sesiones, tasa de
 * cancelación/ausencia, calificación promedio, ratio de conversión y alerta
 * de especialistas inactivos.
 */
class PerformanceReportService
{
    /** Días sin actividad para marcar a un especialista como inactivo. */
    private const INACTIVE_DAYS = 15;

    public function getStats(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $p = ReportPeriod::make($period, $startDate, $endDate);

        $especialistas = Especialista::with('usuario')->get();

        // Citas del período por especialista y estado (para volumen y cancelación).
        $citasPeriodo = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$p->start, $p->end])
            ->select('citas.especialista_id', 'citas.estado', DB::raw('SUM(citas.cantidad_sesiones) as total'))
            ->groupBy('citas.especialista_id', 'citas.estado')
            ->get()
            ->groupBy('especialista_id');

        // Calificación promedio y número de reseñas (histórico) por especialista.
        $ratings = DB::table('ratings_especialistas')
            ->select('especialista_id', DB::raw('AVG(rating) as avg_rating'), DB::raw('COUNT(*) as reviews'))
            ->groupBy('especialista_id')
            ->get()
            ->keyBy('especialista_id');

        // Conversión (histórico): pacientes con ≥2 sesiones realizadas vs con ≥1, por especialista.
        $sesionesPacDoc = Cita::where('estado', Cita::STATUS_COMPLETED)
            ->select('especialista_id', 'paciente_id', DB::raw('SUM(cantidad_sesiones) as total'))
            ->groupBy('especialista_id', 'paciente_id')
            ->get()
            ->groupBy('especialista_id');

        // Última fecha de sesión por especialista (para detectar inactividad).
        $ultimaSesion = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->select('citas.especialista_id', DB::raw('MAX(disponibilidades.fecha) as ultima'))
            ->groupBy('citas.especialista_id')
            ->pluck('ultima', 'especialista_id');

        $inactiveThreshold = now()->subDays(self::INACTIVE_DAYS)->startOfDay();
        $ranking = [];
        $inactivos = [];

        foreach ($especialistas as $esp) {
            $porEstado = $citasPeriodo->get($esp->id, collect())->pluck('total', 'estado');
            $realizadas = (int) ($porEstado[Cita::STATUS_COMPLETED] ?? 0);
            $canceladas = (int) ($porEstado[Cita::STATUS_CANCELLED] ?? 0);
            $totalCitas = (int) $porEstado->sum();
            $cancelRate = $totalCitas > 0 ? ($canceladas / $totalCitas) * 100 : 0;

            $ratingRow = $ratings->get($esp->id);
            $avgRating = $ratingRow ? round((float) $ratingRow->avg_rating, 1) : 0;
            $reviews   = $ratingRow ? (int) $ratingRow->reviews : 0;

            $pacientes = $sesionesPacDoc->get($esp->id, collect());
            $conUno    = $pacientes->count();
            $recurrent = $pacientes->filter(fn ($r) => $r->total >= 2)->count();
            $conversion = $conUno > 0 ? ($recurrent / $conUno) * 100 : 0;

            $ranking[] = [
                'id'          => $esp->id,
                'nombre'      => $esp->usuario->nombre ?? 'Especialista',
                'especialidad'=> $esp->especialidad,
                'foto_url'    => $esp->foto_url,
                'realizadas'  => $realizadas,
                'total_citas' => $totalCitas,
                'cancel_rate' => round($cancelRate, 1),
                'avg_rating'  => $avgRating,
                'reviews'     => $reviews,
                'conversion'  => round($conversion, 1),
            ];

            $ultima = $ultimaSesion[$esp->id] ?? null;
            $ultimaCarbon = $ultima ? \Carbon\Carbon::parse($ultima) : null;
            if (! $ultimaCarbon || $ultimaCarbon->lt($inactiveThreshold)) {
                $inactivos[] = [
                    'id'            => $esp->id,
                    'nombre'        => $esp->usuario->nombre ?? 'Especialista',
                    'especialidad'  => $esp->especialidad,
                    'foto_url'      => $esp->foto_url,
                    'ultima_sesion' => $ultimaCarbon?->format('d/m/Y'),
                    'dias'          => $ultimaCarbon ? $ultimaCarbon->diffInDays(now()) : null,
                ];
            }
        }

        // Ordenar ranking por sesiones realizadas (desc).
        usort($ranking, fn ($a, $b) => $b['realizadas'] <=> $a['realizadas']);

        return [
            'period'       => $p->key,
            'period_label' => $p->label,
            'ranking'      => $ranking,
            'inactivos'    => $inactivos,
            'inactive_days'=> self::INACTIVE_DAYS,
        ];
    }
}
