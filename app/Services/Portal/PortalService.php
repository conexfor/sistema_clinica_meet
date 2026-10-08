<?php

namespace App\Services\Portal;

use App\Models\Cita;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PortalService
{
    /**
     * Obtiene las estadísticas estructuradas para el dashboard del Paciente (Portal),
     * adaptadas al modelo analítico premium (DentalPro Replica).
     */
    public function getDashboardStats(int $pacienteId, string $period = 'month'): array
    {
        $now = now();
        
        // --- 1. Calcular KPIs Principales (Patient Context) con datos reales ---
        // Sesiones asistidas (sumatoria de cantidad_sesiones para citas finalizadas)
        $citasAsistidas = Cita::where('paciente_id', $pacienteId)
            ->where('estado', Cita::STATUS_COMPLETED)
            ->sum('cantidad_sesiones');

        // Próxima cita (reservada / en atención) — fecha más cercana en o después de hoy
        $proximaCita = Cita::with('disponibilidad')
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.paciente_id', $pacienteId)
            ->whereIn('citas.estado', [Cita::STATUS_RESERVADA, Cita::STATUS_ATTENTION])
            ->where('disponibilidades.fecha', '>=', $now->toDateString())
            ->orderBy('disponibilidades.fecha', 'asc')
            ->orderBy('disponibilidades.hora_inicio', 'asc')
            ->select('citas.*')
            ->first();

        $diasProxima = null;
        if ($proximaCita && $proximaCita->disponibilidad?->fecha) {
            $diasProxima = $now->startOfDay()->diffInDays(Carbon::parse($proximaCita->disponibilidad->fecha)->startOfDay(), false);
        }

        // Gastos totales: suma de totales registrados en citas realizadas
        $gastosTotales = Cita::where('paciente_id', $pacienteId)
            ->where('estado', Cita::STATUS_COMPLETED)
            ->sum('total');

        // Terapias activas: especialistas con al menos una cita relevante (reservada/atención/realizada)
        $terapiasActivas = Cita::where('paciente_id', $pacienteId)
            ->whereIn('estado', [Cita::STATUS_RESERVADA, Cita::STATUS_ATTENTION, Cita::STATUS_COMPLETED])
            ->distinct()
            ->count('especialista_id');

        return [
            'current_period' => $period,
            'kpis' => [
                'citas_asistidas' => (int) $citasAsistidas,
                'dias_proxima'    => $diasProxima,
                'gastos_totales'  => (float) $gastosTotales,
                'terapias_activas'=> (int) $terapiasActivas,
            ],
            'charts' => [],
        ];
    }

    /**
     * Obtener citas para un paciente específico.
     */
    public function getPatientAppointments(int $pacienteId, ?int $limit = null, array $statuses = [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_COMPLETED, Cita::STATUS_CANCELLED], string $direction = 'asc', bool $onlyUpcoming = false): Collection
    {
        $query = Cita::with(['especialista.usuario', 'disponibilidad'])
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.paciente_id', $pacienteId)
            ->whereIn('citas.estado', $statuses)
            ->select('citas.*')
            ->orderBy('disponibilidades.fecha', $direction)
            ->orderBy('disponibilidades.hora_inicio', $direction);

        if ($onlyUpcoming) {
            $query->where('disponibilidades.fecha', '>=', now()->toDateString());
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
