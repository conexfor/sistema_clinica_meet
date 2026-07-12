<?php

namespace App\Observers;

use App\Models\Cita;
use App\Services\Rating\RatingService;
use App\Services\Waitlist\WaitlistService;

class CitaObserver
{
    public function __construct(
        protected RatingService $ratingService,
        protected WaitlistService $waitlistService
    ) {}
    /**
     * Handle the Cita "created" event.
     */
    public function created(Cita $cita): void
    {
        //
    }

    /**
     * Handle the Cita "updated" event.
     */
    public function updated(Cita $cita): void
    {
        if ($cita->isDirty('estado')) {
            if ($cita->estado === Cita::STATUS_CANCELLED) {
                $this->releaseSlots($cita);
                // Relleno por cancelaciones: avisar a los admins si hay pacientes
                // esperando a este especialista (las reprogramaciones usan otro
                // estado, así que no disparan aquí).
                $this->waitlistService->handleFreedSlot($cita);
            }

            // Recalcular métricas si el estado cambia a final/relevante
            if (in_array($cita->estado, [Cita::STATUS_COMPLETED, Cita::STATUS_CANCELLED])) {
                $this->ratingService->recalculateMetrics($cita->especialista_id);
            }
        }
    }

    /**
     * Handle the Cita "deleted" event.
     */
    public function deleted(Cita $cita): void
    {
        $this->releaseSlots($cita);
    }

    /**
     * Liberar los slots asociados a una cita.
     */
    private function releaseSlots(Cita $cita): void
    {
        $slot = $cita->disponibilidad;
        if ($slot) {
            // Liberar el slot principal
            $slot->update(['esta_reservada' => false]);
            
            // Si es el modelo de bloques de 1h (sin tipo_consulta), liberar los consecutivos
            if (!$cita->tipo_consulta) {
                $fecha = $slot->fecha->toDateString();
                $horaInicio = $slot->hora_inicio;
                $cantidad = $cita->cantidad_sesiones;
                
                for ($i = 1; $i < $cantidad; $i++) {
                    $nextStart = \Carbon\Carbon::parse($horaInicio)->addHours($i)->format('H:i:s');
                    \App\Models\Disponibilidad::where([
                        'especialista_id' => $cita->especialista_id,
                        'fecha' => $fecha,
                        'hora_inicio' => $nextStart
                    ])->update(['esta_reservada' => false]);
                }
            }
        }
    }

    /**
     * Handle the Cita "restored" event.
     */
    public function restored(Cita $cita): void
    {
        //
    }

    /**
     * Handle the Cita "force deleted" event.
     */
    public function forceDeleted(Cita $cita): void
    {
        //
    }
}
