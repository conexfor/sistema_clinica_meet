<?php

namespace App\Services\Booking;

use App\Models\Disponibilidad;
use App\Models\Cita;
use App\Models\Especialista;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\Google\GoogleMeetingService;
use App\Models\Modalidad;
use App\Models\HorarioSemanal;

class BookingService
{
    public function __construct(
        protected GoogleMeetingService $googleMeetingService
    ) {}
    /**
     * Convertir un ID de slot (físico o virtual) en un registro de Disponibilidad real.
     */
    public function materializeSlot(string $id): Disponibilidad
    {
        $parts = explode(':', $id);
        
        if (str_starts_with($id, 'weekly:')) {
            $weeklyId = $parts[1];
            $fecha = $parts[2];
            $slotOffset = (count($parts) > 4 && $parts[3] === 'slot') ? (int)$parts[4] : 0;

            $weekly = HorarioSemanal::findOrFail($weeklyId);
            $start = Carbon::parse($weekly->hora_inicio)->addHours($slotOffset);

            return Disponibilidad::firstOrCreate([
                'especialista_id' => $weekly->especialista_id,
                'fecha' => $fecha,
                'hora_inicio' => $start->format('H:i:s'),
                'hora_fin' => $start->copy()->addHour()->format('H:i:s'),
            ], [
                'esta_reservada' => false
            ]);
        }

        // Handle physical slots with potential explosion offset
        $physicalId = $parts[0];
        $slotOffset = (count($parts) > 2 && $parts[1] === 'slot') ? (int)$parts[2] : 0;
        
        $bloque = Disponibilidad::findOrFail($physicalId);
        
        if ($slotOffset > 0) {
            $start = Carbon::parse($bloque->hora_inicio)->addHours($slotOffset);
            
            // If the original block was long, we materialize a specific 1h block for this booking
            // so we don't reserve a huge block if we only pick an hour mid-way.
            return Disponibilidad::firstOrCreate([
                'especialista_id' => $bloque->especialista_id,
                'fecha' => $bloque->fecha->toDateString(),
                'hora_inicio' => $start->format('H:i:s'),
                'hora_fin' => $start->copy()->addHour()->format('H:i:s'),
            ], [
                'esta_reservada' => false
            ]);
        }

        return $bloque;
    }

    /**
     * Obtener bloques disponibles agrupados por periodo para una fecha dada.
     * Soporta filtrado por duración (cantidad de sesiones consecutivas).
     */
    public function getAvailableSlots(?string $fecha = null, int $cantidadSesiones = 1): array
    {
        $fecha = $fecha ?: Carbon::today()->toDateString();
        $date = Carbon::parse($fecha);
        $diaSemana = $date->dayOfWeek;

        // 1. Obtener bloques físicos específicos ya creados
        $bloquesFisicos = Disponibilidad::where('fecha', $fecha)
            ->where('esta_reservada', false)
            ->get();

        // 2. Obtener horarios semanales recurrentes para este día
        $horariosSemanales = HorarioSemanal::where('dia_semana', $diaSemana)
            ->where('esta_activo', true)
            ->get();

        $allPossibleSlots = collect();

        // Añadir bloques físicos
        foreach ($bloquesFisicos as $bloque) {
            $start = Carbon::parse($bloque->hora_inicio);
            $end = Carbon::parse($bloque->hora_fin);
            $hours = $start->diffInHours($end);

            for ($i = 0; $i < $hours; $i++) {
                $currentStart = $start->copy()->addHours($i);
                $allPossibleSlots->push([
                    'id' => (string)$bloque->id . ":slot:{$i}",
                    'original_id' => (string)$bloque->id,
                    'type' => 'physical',
                    'hora_inicio' => $currentStart->format('H:i:s'),
                    'hora_fin' => $currentStart->copy()->addHour()->format('H:i:s'),
                    'doctor_inicio' => $bloque->hora_inicio,
                    'doctor_fin' => $bloque->hora_fin,
                    'especialista_id' => $bloque->especialista_id,
                    'especialista' => $bloque->especialista,
                    'fecha' => $bloque->fecha,
                ]);
            }
        }

        // Añadir virtuales si no hay un bloque físico que lo solape
        foreach ($horariosSemanales as $weekly) {
            $start = Carbon::parse($weekly->hora_inicio);
            $end = Carbon::parse($weekly->hora_fin);
            $hours = $start->diffInHours($end);

            for ($i = 0; $i < $hours; $i++) {
                $currentStart = $start->copy()->addHours($i);
                $formattedStart = $currentStart->format('H:i:s');
                
                $exists = $bloquesFisicos->where('especialista_id', $weekly->especialista_id)
                    ->where('hora_inicio', $formattedStart)
                    ->isNotEmpty();
                    
                if (!$exists) {
                    // Calcular la hora de fin de este slot virtual (siempre 1h)
                    $slotStart = $currentStart->copy();
                    $slotEnd   = $currentStart->copy()->addHour();

                    /**
                     * Verificar si alguna Cita activa SOLAPA con este rango horario.
                     * Condición de solapamiento estándar:
                     *   cita.hora_inicio < slot.hora_fin  AND  cita.hora_fin > slot.hora_inicio
                     *
                     * Esto cubre casos como:
                     *  - Cita 2:00-3:00 solapa con slot virtual 2:30-3:30 ✅
                     *  - Cita 1:00-2:00 NO solapa con slot 2:00-3:00 ✅
                     */
                    $reservado = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                        ->where('citas.especialista_id', $weekly->especialista_id)
                        ->where('disponibilidades.fecha', $fecha)
                        ->whereIn('citas.estado', [
                            Cita::STATUS_RESERVADA,
                            Cita::STATUS_RESCHEDULED,
                            Cita::STATUS_ATTENTION,
                        ])
                        // La cita empieza ANTES del fin del slot Y termina DESPUÉS del inicio del slot
                        ->where('disponibilidades.hora_inicio', '<', $slotEnd->format('H:i:s'))
                        ->whereRaw("ADDTIME(disponibilidades.hora_inicio, SEC_TO_TIME(citas.cantidad_sesiones * 3600)) > ?", [$slotStart->format('H:i:s')])
                        ->exists();

                    if (!$reservado) {
                        $allPossibleSlots->push([
                            'id'             => "weekly:{$weekly->id}:{$fecha}:slot:{$i}",
                            'original_id'    => "weekly:{$weekly->id}:{$fecha}",
                            'type'           => 'virtual',
                            'hora_inicio'    => $slotStart->format('H:i:s'),
                            'hora_fin'       => $slotEnd->format('H:i:s'),
                            'doctor_inicio'  => $weekly->hora_inicio,
                            'doctor_fin'     => $weekly->hora_fin,
                            'especialista_id' => $weekly->especialista_id,
                            'especialista'   => $weekly->especialista,
                            'fecha'          => $date->copy(),
                        ]);
                    }
                }
            }
        }

        $allPossibleSlots = $allPossibleSlots->sortBy('hora_inicio');

        // 3. Filtrar por bloques consecutivos si cantidadSesiones > 1
        $filteredSlots = collect();
        
        foreach ($allPossibleSlots as $slotData) {
            $slot = (array)$slotData;
            $hasConsecutive = true;
            $currentStart = Carbon::parse($slot['hora_inicio']);
            $doctorEnd = Carbon::parse($slot['doctor_fin']);
            
            // Verificamos si el tiempo requerido cabe dentro del RANGO DEL DOCTOR
            $requiredEnd = $currentStart->copy()->addHours($cantidadSesiones);
            
            if ($requiredEnd->greaterThan($doctorEnd)) {
                $hasConsecutive = false;
            } else {
                // También verificamos si los bloques intermedios están ocupados (para bloques físicos u otros virtuales)
                for ($j = 1; $j < $cantidadSesiones; $j++) {
                    $intermediateStart = $currentStart->copy()->addHours($j)->format('H:i:s');
                    $isOccupied = false;
                    
                    $physSlot = Disponibilidad::where('especialista_id', $slot['especialista_id'])
                        ->where('fecha', $fecha)
                        ->where('hora_inicio', $intermediateStart)
                        ->first();
                        
                    if ($physSlot && $physSlot->esta_reservada) {
                        // Verificar si hay una cita que ocupe este bloque (ya sea directamente o por duración)
                        $hasCita = Cita::where('disponibilidad_id', $physSlot->id)
                            ->whereIn('estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])
                            ->exists();
                            
                        // Si no hay cita directa, podría ser un bloque consecutivo de una cita que empezó antes
                        if (!$hasCita) {
                            $hasCita = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                                ->where('citas.especialista_id', $slot['especialista_id'])
                                ->where('disponibilidades.fecha', $fecha)
                                ->whereIn('citas.estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])
                                ->get()
                                ->filter(function($c) use ($intermediateStart) {
                                    $cStart = \Carbon\Carbon::parse($c->disponibilidad->hora_inicio);
                                    $cEnd = $cStart->copy()->addHours($c->cantidad_sesiones);
                                    $target = \Carbon\Carbon::parse($intermediateStart);
                                    return $target->between($cStart, $cEnd->subMinute());
                                })->isNotEmpty();
                        }
                        
                        if ($hasCita) {
                            $isOccupied = true;
                        } else {
                            // Auto-fix orphan slot
                            $physSlot->update(['esta_reservada' => false]);
                            $isOccupied = false;
                        }
                    }
                        
                    if ($isOccupied) {
                        $hasConsecutive = false;
                        break;
                    }
                }
            }
            
            $slot['can_fulfill_duration'] = $hasConsecutive;
            $slot['actual_sessions'] = $hasConsecutive ? $cantidadSesiones : 1;
            $filteredSlots->push((array)$slot);
        }

        return [
            'morning' => $filteredSlots->filter(fn($s) => Carbon::parse($s['hora_inicio'])->hour < 12),
            'afternoon' => $filteredSlots->filter(fn($s) => Carbon::parse($s['hora_inicio'])->hour >= 12 && Carbon::parse($s['hora_inicio'])->hour < 18),
            'evening' => $filteredSlots->filter(fn($s) => Carbon::parse($s['hora_inicio'])->hour >= 18),
        ];
    }

    /**
     * Crear una nueva cita y marcar el bloque(s) como reservado.
     */
    public function createAppointment(array $data): Cita
    {
        return DB::transaction(function () use ($data) {
            $cantidadSesiones = $data['cantidad_sesiones'] ?? 1;
            $tipoConsulta = $data['tipo_consulta'] ?? null;
            $duracionMinutos = $data['duracion_minutos'] ?? null;
            
            // Si es un slot médico dinámico, ya viene materializado del checkout
            $mainSlot = Disponibilidad::findOrFail($data['disponibilidad_id']);
            
            if ($mainSlot->esta_reservada) {
                $hasActiveCita = Cita::where('disponibilidad_id', $mainSlot->id)
                    ->whereIn('estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])
                    ->exists();

                if ($hasActiveCita) {
                    throw new \Exception('Este horario ya no está disponible.');
                } else {
                    // Auto-fix orphan slot
                    $mainSlot->update(['esta_reservada' => false]);
                }
            }

            $slotsToReserve = collect([$mainSlot]);
            $currentStartTime = Carbon::parse($mainSlot->hora_inicio);
            $fecha = $mainSlot->fecha->toDateString();

            // Solo buscar bloques consecutivos si NO es una consulta médica dinámica (modelo viejo de sesiones de 1h)
            if (!$tipoConsulta) {
                for ($i = 1; $i < $cantidadSesiones; $i++) {
                    $nextStart = $currentStartTime->copy()->addHours($i)->format('H:i:s');
                    
                    $nextSlot = Disponibilidad::where([
                        'especialista_id' => $mainSlot->especialista_id,
                        'fecha' => $fecha,
                        'hora_inicio' => $nextStart,
                    ])->first();

                    if (!$nextSlot) {
                        $dayOfWeek = Carbon::parse($fecha)->dayOfWeek;
                        $weekly = HorarioSemanal::where('especialista_id', $mainSlot->especialista_id)
                            ->where('dia_semana', $dayOfWeek)
                            ->where('hora_inicio', '<=', $nextStart)
                            ->where('hora_fin', '>=', Carbon::parse($nextStart)->addHour()->format('H:i:s'))
                            ->where('esta_activo', true)
                            ->first();

                        if ($weekly) {
                            $startShift = Carbon::parse($weekly->hora_inicio);
                            $targetStart = Carbon::parse($nextStart);
                            $offset = $startShift->diffInHours($targetStart);
                            $nextSlot = $this->materializeSlot("weekly:{$weekly->id}:{$fecha}:slot:{$offset}");
                        }
                    }

                    if ($nextSlot) {
                        if ($nextSlot->esta_reservada) {
                            // Check for orphan reserved slots
                            $hasActiveCita = Cita::where('disponibilidad_id', $nextSlot->id)
                                ->whereIn('estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])
                                ->exists();

                            if (!$hasActiveCita) {
                                // Double check if it's covered by another multi-hour session
                                $hasActiveCita = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                                    ->where('citas.especialista_id', $mainSlot->especialista_id)
                                    ->where('disponibilidades.fecha', $fecha)
                                    ->whereIn('citas.estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])
                                    ->get()
                                    ->filter(function($c) use ($nextStart) {
                                        $cStart = Carbon::parse($c->disponibilidad->hora_inicio);
                                        $cEnd = $cStart->copy()->addHours($c->cantidad_sesiones);
                                        $target = Carbon::parse($nextStart);
                                        return $target->between($cStart, $cEnd->subMinute());
                                    })->isNotEmpty();
                            }

                            if ($hasActiveCita) {
                                throw new \Exception("Uno de los bloques consecutivos ({$nextStart}) ya está reservado.");
                            } else {
                                // Auto-fix orphan slot
                                $nextSlot->update(['esta_reservada' => false]);
                            }
                        }
                        $slotsToReserve->push($nextSlot);
                    } else {
                        throw new \Exception("No hay disponibilidad consecutiva suficiente para {$cantidadSesiones} sesiones.");
                    }
                }
            }

            // Marcar todos como reservados
            foreach ($slotsToReserve as $slot) {
                $slot->update(['esta_reservada' => true]);
            }

            // Crear cita - vinculada al primer bloque (inicio)
            $cita = Cita::create([
                'paciente_id' => $data['paciente_id'],
                'especialista_id' => $mainSlot->especialista_id,
                'servicio_id' => $data['servicio_id'] ?? null,
                'disponibilidad_id' => $mainSlot->id,
                'cantidad_sesiones' => $cantidadSesiones,
                'tipo_consulta' => $tipoConsulta,
                'duracion_minutos' => $duracionMinutos,
                'estado' => Cita::STATUS_RESERVADA,
                'modalidad_id' => $data['modalidad_id'] ?? null,
                'notas' => $data['notas'] ?? null,
                'costo' => $data['costo'] ?? 0,
                'total' => $data['total'] ?? 0,
                'metodo_pago' => $data['metodo_pago'] ?? null,
                'tipo_comprobante' => $data['tipo_comprobante'] ?? null,
                'documento_serie' => $data['documento_serie'] ?? null,
                'documento_correlativo' => $data['documento_correlativo'] ?? null,
            ]);

            // Registrar la cita en Google Calendar (virtual y presencial) e invitar
            // a paciente y especialista.
            $this->syncCitaToGoogleCalendar(
                $cita,
                $mainSlot,
                $slotsToReserve->last(),
                $tipoConsulta,
                $cantidadSesiones,
                'Consulta psicológica programada vía ' . config('clinic.name') . '.'
            );

            return $cita;
        });
    }

    /**
     * Reprogramar una cita existente.
     */
    public function rescheduleAppointment(Cita $cita, array $data): Cita
    {
        return DB::transaction(function () use ($cita, $data) {
            // 1. Marcar la cita actual como REPROGRAMADA (No liberamos slots aún si queremos auditoría estricta,
            // pero para disponibilidad real, debemos liberarlos para que el especialista esté libre)
            $oldMainSlot = $cita->disponibilidad;
            if ($oldMainSlot) {
                if (!$cita->tipo_consulta) {
                    $oldSlots = Disponibilidad::where('especialista_id', $cita->especialista_id)
                        ->where('fecha', $oldMainSlot->fecha->toDateString())
                        ->where('hora_inicio', '>=', $oldMainSlot->hora_inicio)
                        ->where('hora_inicio', '<', Carbon::parse($oldMainSlot->hora_inicio)->addHours($cita->cantidad_sesiones)->format('H:i:s'))
                        ->get();
                    
                    foreach ($oldSlots as $slot) {
                        $slot->update(['esta_reservada' => false]);
                    }
                } else {
                    $oldMainSlot->update(['esta_reservada' => false]);
                }
            }

            // Cambiamos el estado de la cita original antes de crear la nueva
            $cita->update(['estado' => Cita::STATUS_RESCHEDULED]);

            // 2. Materializar y Reservar los nuevos slots
            $disponibilidadId = $data['disponibilidad_id'];
            $cantidadSesiones = $data['cantidad_sesiones'] ?? $cita->cantidad_sesiones;
            $tipoConsulta = $data['tipo_consulta'] ?? $cita->tipo_consulta;
            $duracionMinutos = $data['duracion_minutos'] ?? $cita->duracion_minutos;

            // Determinar si es slot médico o tradicional
            $newMainSlot = null;
            if (str_starts_with($disponibilidadId, 'medical:')) {
                $newMainSlot = app(MedicalBookingService::class)->materializeMedicalSlot($disponibilidadId);
            } else {
                $newMainSlot = $this->materializeSlot($disponibilidadId);
            }

            if ($newMainSlot->esta_reservada) {
                throw new \Exception('El nuevo horario seleccionado ya no está disponible.');
            }

            $newSlots = collect([$newMainSlot]);
            if (!$tipoConsulta) {
                // Lógica de bloques de 1h para el modelo tradicional
                $currentStart = Carbon::parse($newMainSlot->hora_inicio);
                for ($i = 1; $i < $cantidadSesiones; $i++) {
                    $nextStart = $currentStart->copy()->addHours($i)->format('H:i:s');
                    $nextSlot = Disponibilidad::where([
                        'especialista_id' => $newMainSlot->especialista_id,
                        'fecha' => $newMainSlot->fecha->toDateString(),
                        'hora_inicio' => $nextStart,
                    ])->first();
                    
                    if (!$nextSlot) {
                        // Intentar materializar de semanal
                        $dayOfWeek = $newMainSlot->fecha->dayOfWeek;
                        $weekly = HorarioSemanal::where('especialista_id', $newMainSlot->especialista_id)
                            ->where('dia_semana', $dayOfWeek)
                            ->where('hora_inicio', '<=', $nextStart)
                            ->where('hora_fin', '>=', Carbon::parse($nextStart)->addHour()->format('H:i:s'))
                            ->where('esta_activo', true)
                            ->first();

                        if ($weekly) {
                            $offset = Carbon::parse($weekly->hora_inicio)->diffInHours(Carbon::parse($nextStart));
                            $nextSlot = $this->materializeSlot("weekly:{$weekly->id}:{$newMainSlot->fecha->toDateString()}:slot:{$offset}");
                        }
                    }

                    if ($nextSlot && !$nextSlot->esta_reservada) {
                        $newSlots->push($nextSlot);
                    } else {
                        throw new \Exception("No hay disponibilidad consecutiva suficiente.");
                    }
                }
            }

            // Marcar nuevos como reservados
            foreach ($newSlots as $slot) {
                $slot->update(['esta_reservada' => true]);
            }

            // 3. Crear una NUEVA cita conservando el historial
            $nuevaCita = Cita::create([
                'paciente_id' => $cita->paciente_id,
                'especialista_id' => $newMainSlot->especialista_id,
                'servicio_id' => $cita->servicio_id, // Conservar el servicio original
                'disponibilidad_id' => $newMainSlot->id,
                'cantidad_sesiones' => $cantidadSesiones,
                'tipo_consulta' => $tipoConsulta,
                'duracion_minutos' => $duracionMinutos,
                'estado' => Cita::STATUS_RESERVADA,
                'modalidad_id' => $data['modalidad_id'] ?? $cita->modalidad_id,
                'notas' => $data['notas'] ?? $cita->notas,
                'cita_original_id' => $cita->id, // Link al historial
                'costo' => $cita->costo, // En reprogramación se suelen mantener costos originales o se recalculan según política
                'total' => $cita->total,
                'metodo_pago' => $cita->metodo_pago,
                'tipo_comprobante' => $cita->tipo_comprobante,
                'documento_serie' => $cita->documento_serie,
                'documento_correlativo' => $cita->documento_correlativo,
            ]);

            // 4. Registrar la cita reprogramada en Google Calendar (virtual y presencial)
            // e invitar a paciente y especialista.
            $this->syncCitaToGoogleCalendar(
                $nuevaCita,
                $newMainSlot,
                $newSlots->last(),
                $tipoConsulta,
                $cantidadSesiones,
                'Consulta psicológica reprogramada.'
            );

            return $nuevaCita;
        });
    }

    /**
     * Sincroniza una cita con Google Calendar creando un evento en el calendario del
     * especialista e invitando tanto al paciente como al especialista.
     *
     * - Las citas VIRTUALES incluyen un enlace de Google Meet (se guarda en meeting_url).
     * - Las citas PRESENCIALES también quedan registradas en ambos calendarios (sin Meet).
     *
     * Si el especialista no tiene Google conectado, se registra una advertencia y la
     * reserva continúa normalmente (la cita ya está guardada en la base de datos).
     */
    private function syncCitaToGoogleCalendar(
        Cita $cita,
        Disponibilidad $mainSlot,
        Disponibilidad $lastSlot,
        ?string $tipoConsulta,
        int $cantidadSesiones,
        string $descripcion
    ): void {
        try {
            $cita->load(['paciente', 'especialista.usuario']);
            $especialistaUser = $cita->especialista->usuario ?? null;

            if (!$especialistaUser || empty($especialistaUser->google_access_token)) {
                Log::warning("Cita {$cita->id}: el especialista no tiene Google Calendar conectado; no se creó el evento.");
                return;
            }

            $isVirtual = ((int) $cita->modalidad_id === Modalidad::VIRTUAL);

            // Invitar a paciente y especialista para que la cita quede en ambos calendarios
            $attendees = array_values(array_filter([
                $cita->paciente->email    ?? null,
                $especialistaUser->email  ?? null,
            ]));

            $tipoNombre = $tipoConsulta
                ? (MedicalBookingService::CONSULTATION_TYPES[$tipoConsulta]['name'] ?? 'Sesión')
                : 'Sesión ' . $cantidadSesiones . 'h';

            $result = $this->googleMeetingService->createEvent($especialistaUser, [
                'title'       => $tipoNombre . ': ' . ($cita->paciente->nombre ?? 'Paciente'),
                'description' => $descripcion,
                'start_time'  => Carbon::parse($mainSlot->fecha->toDateString() . ' ' . $mainSlot->hora_inicio)->toRfc3339String(),
                'end_time'    => Carbon::parse($lastSlot->fecha->toDateString() . ' ' . $lastSlot->hora_fin)->toRfc3339String(),
                'attendees'   => $attendees,
            ], $isVirtual);

            // Guardar el enlace de Meet solo si la cita es virtual
            if (!empty($result['meet_url'])) {
                $cita->update(['meeting_url' => $result['meet_url']]);
            }
        } catch (\Exception $e) {
            Log::error("Error al sincronizar la cita {$cita->id} con Google Calendar: " . $e->getMessage());
        }
    }
}
