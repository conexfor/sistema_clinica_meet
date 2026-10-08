<?php

namespace App\Services\Admin;

use App\Models\Cita;
use Carbon\Carbon;

/**
 * DrillDownService
 * "Modo Lupa": devuelve la lista real de registros detrás de un KPI clave.
 * Cada resultado tiene la forma { count, items: [{ titulo, subtitulo, extra }] }.
 */
class DrillDownService
{
    /** Métricas soportadas para el drill-down. */
    public const METRICS = ['active_patients', 'sessions', 'no_shows', 'cancellations'];

    private const LIMIT = 150;

    public function resolve(string $metric, string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        return match ($metric) {
            'active_patients' => $this->activePatients($startDate, $endDate),
            'sessions'        => $this->sessions($period, $startDate, $endDate),
            'no_shows'        => $this->noShows($period, $startDate, $endDate),
            'cancellations'   => $this->cancellations($startDate, $endDate),
            default           => ['count' => 0, 'items' => []],
        };
    }

    /** Pacientes con ≥1 sesión (realizada/confirmada) en los últimos 30 días. */
    private function activePatients(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Cita::with(['paciente', 'especialista.usuario', 'disponibilidad'])
            ->whereIn('citas.estado', [Cita::STATUS_COMPLETED, Cita::STATUS_RESERVADA])
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id');

        if ($startDate && $endDate) {
            $query->whereBetween('disponibilidades.fecha', [$startDate, $endDate]);
        } else {
            $query->where('disponibilidades.fecha', '>=', now()->subDays(30)->startOfDay());
        }

        $citas = $query->orderByDesc('disponibilidades.fecha')
            ->select('citas.*')
            ->get()
            ->unique('paciente_id')
            ->take(self::LIMIT);

        $items = $citas->map(fn ($c) => [
            'titulo'    => $c->paciente->nombre ?? 'Paciente',
            'subtitulo' => $c->paciente->email ?? '',
            'extra'     => 'Últ.: ' . optional($c->disponibilidad?->fecha)->format('d/m/Y'),
        ])->values();

        return ['count' => $items->count(), 'items' => $items];
    }

    /** Sesiones realizadas dentro del período. */
    private function sessions(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $p = ReportPeriod::make($period, $startDate, $endDate);

        $citas = Cita::with(['paciente', 'especialista.usuario', 'disponibilidad'])
            ->where('citas.estado', Cita::STATUS_COMPLETED)
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$p->start, $p->end])
            ->orderByDesc('disponibilidades.fecha')
            ->select('citas.*')
            ->limit(self::LIMIT)
            ->get();

        $items = $citas->map(fn ($c) => [
            'titulo'    => $c->paciente->nombre ?? 'Paciente',
            'subtitulo' => ($c->especialista->usuario->nombre ?? 'Especialista') . ' · ' . optional($c->disponibilidad?->fecha)->format('d/m/Y'),
            'extra'     => 'S/ ' . number_format((float) $c->total, 0),
        ])->values();

        return ['count' => $citas->sum('cantidad_sesiones'), 'items' => $items];
    }

    /** Inasistencias reales (estado no_asistio) dentro del período. */
    private function noShows(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $p = ReportPeriod::make($period, $startDate, $endDate);

        $citas = Cita::with(['paciente', 'especialista.usuario', 'disponibilidad'])
            ->where('citas.estado', Cita::STATUS_NO_SHOW)
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$p->start, $p->end])
            ->orderByDesc('disponibilidades.fecha')
            ->select('citas.*')
            ->limit(self::LIMIT)
            ->get();

        $items = $citas->map(fn ($c) => [
            'titulo'    => $c->paciente->nombre ?? 'Paciente',
            'subtitulo' => ($c->especialista->usuario->nombre ?? 'Especialista') . ' · ' . optional($c->disponibilidad?->fecha)->format('d/m/Y'),
            'extra'     => 'No asistió',
        ])->values();

        return ['count' => $items->count(), 'items' => $items];
    }

    /** Cancelaciones registradas en los últimos 7 días o en el rango personalizado indicado. */
    private function cancellations(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Cita::with(['paciente', 'especialista.usuario', 'disponibilidad'])
            ->where('estado', Cita::STATUS_CANCELLED);

        if ($startDate && $endDate) {
            $query->whereBetween('updated_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
        } else {
            $query->whereBetween('updated_at', [now()->subDays(7), now()]);
        }

        $citas = $query->orderByDesc('updated_at')
            ->limit(self::LIMIT)
            ->get();

        $items = $citas->map(fn ($c) => [
            'titulo'    => $c->paciente->nombre ?? 'Paciente',
            'subtitulo' => ($c->especialista->usuario->nombre ?? 'Especialista') . ' · ' . ($c->cancelado_por ?? 'Cancelada'),
            'extra'     => optional($c->updated_at)->format('d/m H:i'),
        ])->values();

        return ['count' => $items->count(), 'items' => $items];
    }
}
