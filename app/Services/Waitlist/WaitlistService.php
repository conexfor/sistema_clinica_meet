<?php

namespace App\Services\Waitlist;

use App\Models\Cita;
use App\Models\ListaEspera;
use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Support\Collection;

/**
 * WaitlistService
 * Motor de la lista de espera. Cuando se libera un cupo (cancelación de una
 * cita), busca a los pacientes en espera para ese especialista y avisa a los
 * administradores para que gestionen el relleno (Variante A: aviso al admin).
 */
class WaitlistService
{
    /**
     * Pacientes pendientes en lista de espera que encajan con el cupo liberado.
     * Criterio: mismo especialista y, si la entrada especifica modalidad, que coincida.
     * Orden FIFO (el que esperó primero, primero).
     */
    public function matchesForFreedSlot(Cita $cita): Collection
    {
        return ListaEspera::pendientes()
            ->where('especialista_id', $cita->especialista_id)
            ->where(function ($q) use ($cita) {
                $q->whereNull('modalidad_id')
                  ->orWhere('modalidad_id', $cita->modalidad_id);
            })
            ->with('paciente')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Maneja un cupo liberado por cancelación: si hay pacientes esperando a ese
     * especialista, notifica a los administradores.
     */
    public function handleFreedSlot(Cita $cita): void
    {
        if (! $cita->especialista_id) {
            return;
        }

        $matches = $this->matchesForFreedSlot($cita);

        if ($matches->isEmpty()) {
            return;
        }

        $especialista = optional($cita->especialista?->usuario)->nombre ?? 'un especialista';

        $cuando = '';
        if ($cita->disponibilidad) {
            $fecha = $cita->disponibilidad->fecha instanceof \Carbon\Carbon
                ? $cita->disponibilidad->fecha
                : \Carbon\Carbon::parse($cita->disponibilidad->fecha);
            $hora = substr((string) $cita->disponibilidad->hora_inicio, 0, 5);
            $cuando = ' el ' . $fecha->locale('es')->isoFormat('D [de] MMMM') . ' a las ' . $hora;
        }

        $mensaje = 'Se liberó un cupo con ' . $especialista . $cuando . '. '
            . $matches->count() . ' paciente(s) en lista de espera para este especialista.';

        $this->notifyAdmins('Cupo liberado · lista de espera', $mensaje);
    }

    /**
     * Aviso a los administradores cuando un paciente se suma a la lista de espera.
     */
    public function notifyNewEntry(ListaEspera $entry): void
    {
        $paciente = $entry->nombre_mostrado;
        $especialista = optional($entry->especialista?->usuario)->nombre;

        $mensaje = $paciente . ' se sumó a la lista de espera'
            . ($especialista ? ' para ' . $especialista : '')
            . '. Revisa si puedes ofrecerle un cupo.';

        $this->notifyAdmins('Nuevo paciente en lista de espera', $mensaje);
    }

    /**
     * Notifica a todos los administradores con un enlace a la lista de espera pendiente.
     */
    private function notifyAdmins(string $titulo, string $mensaje): void
    {
        $url = route('admin.lista-espera.index', ['estado' => 'pendiente'], false);

        $adminIds = Usuario::whereHas('rol', fn ($q) => $q->where('nombre', Usuario::ROL_ADMIN))
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            Notificacion::crear($adminId, 'general', $titulo, $mensaje, $url);
        }
    }
}
