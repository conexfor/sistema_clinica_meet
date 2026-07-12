<?php

namespace App\Services\Booking;

use App\Models\Disponibilidad;
use App\Models\Cita;
use App\Models\HorarioSemanal;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Servicio especializado en lógica de reservas médicas con duraciones dinámicas.
 */
class MedicalBookingService
{
    /**
     * Configuracion de los tipos de consulta y sus duraciones en minutos.
     */
    public const CONSULTATION_TYPES = [
        'standard' => [
            'name' => 'Sesión de Terapia',
            'min_duration' => 30,
            'max_duration' => 30,
            'step' => 30,
        ],
    ];

    /**
     * Calcula los slots disponibles dinámicamente.
     */
    public function getAvailableSlots(int $especialistaId, string $fecha, string $typeCode, int $sessions = 1, ?int $customDuration = null): array
    {
        $consultation = self::CONSULTATION_TYPES[$typeCode] ?? self::CONSULTATION_TYPES['standard'];
        // 0. Obtener el especialista
        $especialista = \App\Models\Especialista::with('usuario')->findOrFail($especialistaId);
        
        // Usar duracion personalizada si se proporciona
        $baseDuration = $customDuration ?? $consultation['max_duration'];
        $duration = $baseDuration * $sessions; // Duración total multiplicado por sesiones
        $bufferMinutes = -20; // Regla de los 20 minutos

        // 1. Obtener jornada del doctor (bloques base)
        $shifts = $this->getDoctorShifts($especialistaId, $fecha);
        
        // 2. Obtener citas activas existentes (excluyendo canceladas o reprogramadas)
        $appointments = Cita::where('especialista_id', $especialistaId)
            ->whereHas('disponibilidad', function ($query) use ($fecha) {
                $query->where('fecha', $fecha);
            })
            ->whereIn('estado', [
                Cita::STATUS_RESERVADA,
                Cita::STATUS_ATTENTION,
                Cita::STATUS_COMPLETED
            ])
            ->with('disponibilidad')
            ->get();

        $availabilityByPeriod = [
            'morning' => collect(),
            'afternoon' => collect(),
            'evening' => collect(),
        ];

        foreach ($shifts as $shift) {
            $freeSegments = $this->calculateFreeSegments((array)$shift, $appointments);
            
            foreach ($freeSegments as $segment) {
                // Pasamos la duración total (calculada con el múltiplo de sesiones)
                $slots = $this->generateSlotsInSegment($segment, $duration, $fecha, $bufferMinutes, $especialista, $typeCode);
                
                foreach ($slots as $slot) {
                    $hour = Carbon::parse($slot['start'])->hour;
                    if ($hour < 12) $availabilityByPeriod['morning']->push((array)$slot);
                    elseif ($hour < 18) $availabilityByPeriod['afternoon']->push((array)$slot);
                    else $availabilityByPeriod['evening']->push((array)$slot);
                }
            }
        }

        return [
            'morning' => $availabilityByPeriod['morning']->unique('start')->values(),
            'afternoon' => $availabilityByPeriod['afternoon']->unique('start')->values(),
            'evening' => $availabilityByPeriod['evening']->unique('start')->values(),
        ];
    }

    /**
     * Obtiene los bloques de jornada definidos para el doctor.
     */
    private function getDoctorShifts(int $especialistaId, string $fecha): Collection
    {
        $date = Carbon::parse($fecha);
        
        // Físicos
        $physical = Disponibilidad::where('especialista_id', $especialistaId)
            ->where('fecha', $fecha)
            ->where('esta_reservada', false)
            ->get(['hora_inicio', 'hora_fin']);

        // Semanales
        $weekly = HorarioSemanal::where('especialista_id', $especialistaId)
            ->where('dia_semana', $date->dayOfWeek)
            ->where('esta_activo', true)
            ->get(['hora_inicio', 'hora_fin']);

        return $physical->concat($weekly)->map(fn($s) => [
            'start' => $s->hora_inicio,
            'end' => $s->hora_fin,
        ]);
    }

    /**
     * Resta las citas ocupadas de un bloque de jornada para encontrar segmentos libres.
     */
    private function calculateFreeSegments(array $shift, Collection $appointments): array
    {
        $segments = [ [
            'start' => Carbon::parse($shift['start']),
            'end' => Carbon::parse($shift['end'])
        ] ];

        foreach ($appointments as $app) {
            $appStart = Carbon::parse($app->disponibilidad->hora_inicio);
            
            // La duración total de la cita se calcula multiplicando el tiempo base por las sesiones reales
            $baseDur = $app->duracion_minutos ?? 60;
            $appEnd = $appStart->copy()->addMinutes($baseDur * max(1, $app->cantidad_sesiones));

            $newSegments = [];
            foreach ($segments as $seg) {
                // Si la cita está fuera del segmento, mantenerlo tal cual
                if ($appEnd->lte($seg['start']) || $appStart->gte($seg['end'])) {
                    $newSegments[] = $seg;
                    continue;
                }

                // Fragmentar el segmento
                if ($appStart->gt($seg['start'])) {
                    $newSegments[] = ['start' => $seg['start'], 'end' => $appStart];
                }
                if ($appEnd->lt($seg['end'])) {
                    $newSegments[] = ['start' => $appEnd, 'end' => $seg['end']];
                }
            }
            $segments = $newSegments;
        }

        return $segments;
    }

    /**
     * Genera puntos de inicio válidos dentro de un segmento libre.
     *
     * El paso (step) entre slots es igual a la duración de cada sesión,
     * de forma que los slots se encadenan de forma natural:
     *   - Sesión de 45 min a las 1:00 → siguiente disponible a las 1:45
     *   - Sesión de 60 min a las 1:00 → siguiente disponible a las 2:00
     *
     * @param array              $segment   Segmento libre ['start' => Carbon, 'end' => Carbon]
     * @param int                $duration  Duración total de la reserva en minutos
     * @param string             $fecha     Fecha YYYY-MM-DD
     * @param int                $buffer    Minutos de anticipación mínima (negativo = antes de la hora)
     * @param \App\Models\Especialista $especialista
     * @param string             $typeCode  Tipo de consulta
     */
    private function generateSlotsInSegment(array $segment, int $duration, string $fecha, int $buffer, \App\Models\Especialista $especialista, string $typeCode): array
    {
        $slots = [];

        /*
         * El paso entre slots es la duración total de la reserva ($duration).
         * Esto significa que si pides 2 sesiones de 30 min (60 min total),
         * los slots irán de hora en hora (ej. 7:30 - 8:30, 8:30 - 9:30).
         * Si pides 45 min y 1 sesión, irán cada 45 min.
         */
        $step = $duration;

        // El slot comienza exactamente al inicio del segmento libre (respeta hora de inicio del doctor)
        $current = $segment['start']->copy();

        // El último inicio válido es el final del segmento menos la duración total
        $limit = $segment['end']->copy()->subMinutes($duration);

        $now     = Carbon::now();
        $isToday = Carbon::parse($fecha)->isToday();

        while ($current->lte($limit)) {
            // Regla de anticipación/tolerancia: 
            // Si el turno empezó hace más de X minutos (buffer), ya no se muestra.
            if ($isToday) {
                // abs($buffer) normalmente es 20. El turno caduca 20 mins DESPUÉS de su hora de inicio.
                $threshold = $current->copy()->addMinutes(abs($buffer));
                
                // Si la hora actual es mayor a la hora límite de tolerancia, se descarta este bloque
                if ($now->gt($threshold)) {
                    $current->addMinutes($step);
                    continue;
                }
            }

            // Verificar que el slot cabe completamente dentro del segmento libre
            if ($current->lt($segment['start']) || $current->copy()->addMinutes($duration)->gt($segment['end'])) {
                $current->addMinutes($step);
                continue;
            }

            $slots[] = [
                'id'          => "medical:{$especialista->id}:{$fecha}:{$typeCode}:" . $current->format('H:i:s') . ":{$duration}",
                'start'       => $current->format('H:i:s'),
                'end'         => $current->copy()->addMinutes($duration)->format('H:i:s'),
                'display'     => $current->format('h:i A'),
                'display_end' => $current->copy()->addMinutes($duration)->format('h:i A'),
                'duration'    => $duration,
                'especialista' => $especialista,
            ];

            // Avanzar exactamente por la duración total de la reserva
            $current->addMinutes($step);
        }

        return $slots;
    }

    /**
     * Obtiene una lista de fechas que tienen disponibilidad para un especialista.
     */
    public function getAvailableDates(int $especialistaId, string $referenceDate, int $sessions, int $duration): array
    {
        $date = Carbon::parse($referenceDate);
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();
        
        // No buscar fechas en el pasado
        if ($start->isPast()) {
            $start = Carbon::today();
        }

        $availableDates = [];
        $current = $start->copy();
        
        while ($current->lte($end)) {
            $fecha = $current->toDateString();
            if ($this->hasAvailabilityOnDate($especialistaId, $fecha, $sessions, $duration)) {
                $availableDates[] = $fecha;
            }
            $current->addDay();
        }

        return $availableDates;
    }

    /**
     * Verifica si un especialista tiene alguna disponibilidad en un ventana de días.
     */
    public function hasAvailabilityInWindow(int $especialistaId, int $sessions, int $duration, int $days = 30): bool
    {
        for ($i = 0; $i < $days; $i++) {
            $fecha = Carbon::today()->addDays($i)->toDateString();
            if ($this->hasAvailabilityOnDate($especialistaId, $fecha, $sessions, $duration)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si hay al menos un espacio disponible en una fecha específica.
     */
    public function hasAvailabilityOnDate(int $especialistaId, string $fecha, int $sessions, int $duration): bool
    {
        // Guardián: Si el especialista no tiene ningún turno (físico o semanal) este día, ni intentamos generar slots.
        $shifts = $this->getDoctorShifts($especialistaId, $fecha);
        if ($shifts->isEmpty()) {
            return false;
        }

        // Reutilizamos la lógica de getAvailableSlots pero de forma simplificada para rendimiento
        $slots = $this->getAvailableSlots($especialistaId, $fecha, 'standard', $sessions, $duration);
        
        return !empty($slots['morning']) || !empty($slots['afternoon']) || !empty($slots['evening']);
    }

    /**
     * Materializa un slot dinámico médico en un registro físico de Disponibilidad.
     */
    public function materializeMedicalSlot(string $id): Disponibilidad
    {
        $parts = explode(':', $id);
        // Format: medical:especialista_id:fecha:tipo:hora_inicio:duracion
        $especialistaId = $parts[1];
        $fecha = $parts[2];
        $type = $parts[3];
        $horaInicio = $parts[4] . ':' . $parts[5] . ':' . $parts[6];
        $duracion = (int)$parts[7];

        $start = Carbon::parse($horaInicio);
        $end = $start->copy()->addMinutes($duracion);

        return Disponibilidad::firstOrCreate([
            'especialista_id' => $especialistaId,
            'fecha' => $fecha,
            'hora_inicio' => $start->format('H:i:s'),
            'hora_fin' => $end->format('H:i:s'),
        ], [
            'esta_reservada' => false
        ]);
    }
}
