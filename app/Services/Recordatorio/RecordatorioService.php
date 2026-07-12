<?php

namespace App\Services\Recordatorio;

use App\Models\Cita;
use App\Models\RecordatorioCita;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Gestiona el envío y registro de recordatorios de cita.
 * En desarrollo el mailer está en modo 'log', por lo que los correos quedan
 * registrados en storage/logs y además dejamos constancia en recordatorios_cita.
 */
class RecordatorioService
{
    /**
     * Envía (y registra) recordatorios para las citas activas de una fecha dada.
     * Evita duplicados gracias al registro en recordatorios_cita.
     *
     * @param  string    $fecha           Fecha de las citas (Y-m-d).
     * @param  int|null   $especialistaId  Limitar a un especialista (null = todos).
     * @return int  Cantidad de recordatorios enviados en esta corrida.
     */
    public function enviarParaFecha(string $fecha, ?int $especialistaId = null): int
    {
        $query = Cita::query()
            ->whereIn('estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED])
            ->whereHas('disponibilidad', fn ($q) => $q->whereDate('fecha', $fecha))
            ->whereDoesntHave('recordatorio')
            ->with(['paciente', 'especialista.usuario', 'disponibilidad']);

        if ($especialistaId) {
            $query->where('especialista_id', $especialistaId);
        }

        $citas = $query->get();
        $enviados = 0;

        foreach ($citas as $cita) {
            try {
                $this->enviarUno($cita);
                $enviados++;
            } catch (\Throwable $e) {
                Log::error("Error al enviar recordatorio de la cita {$cita->id}: " . $e->getMessage());
            }
        }

        return $enviados;
    }

    /**
     * Envía el recordatorio de una cita y lo registra.
     */
    protected function enviarUno(Cita $cita): void
    {
        $paciente = $cita->paciente;
        $doctor   = $cita->especialista?->usuario;
        $fecha    = Carbon::parse($cita->disponibilidad->fecha)->locale('es')->isoFormat('dddd D [de] MMMM');
        $hora     = Carbon::parse($cita->disponibilidad->hora_inicio)->format('h:i A');

        if ($paciente?->email) {
            Mail::raw(
                "Hola {$paciente->nombre},\n\n" .
                "Te recordamos tu próxima sesión:\n" .
                "📅 {$fecha}\n🕒 {$hora}\n" .
                "Profesional: Ps. " . ($doctor->nombre ?? 'tu especialista') . "\n\n" .
                "Si no puedes asistir, solicita una reprogramación desde tu portal.\n\nEquipo Sia.",
                function ($message) use ($paciente) {
                    $message->to($paciente->email)->subject('Recordatorio de tu sesión en Sia');
                }
            );
        }

        RecordatorioCita::create([
            'cita_id'         => $cita->id,
            'paciente_id'     => $cita->paciente_id,
            'especialista_id' => $cita->especialista_id,
            'fecha_cita'      => Carbon::parse($cita->disponibilidad->fecha)->toDateString(),
            'canal'           => config('mail.default') === 'log' ? 'log' : 'email',
            'enviado_at'      => now(),
        ]);

        // Notificación en la app (campanita) para el paciente
        \App\Models\Notificacion::crear(
            $cita->paciente_id,
            \App\Models\Notificacion::TIPO_RECORDATORIO,
            'Recordatorio de tu sesión',
            "Tienes una sesión el {$fecha} a las {$hora}" . ($doctor?->nombre ? " con Ps. {$doctor->nombre}" : '') . '.',
            route('portal.index', [], false)
        );
    }

    /**
     * Recordatorios ya enviados para una fecha (para mostrar el log en el panel).
     */
    public function logDeFecha(string $fecha, int $especialistaId)
    {
        return RecordatorioCita::where('especialista_id', $especialistaId)
            ->whereDate('fecha_cita', $fecha)
            ->with('paciente')
            ->latest('enviado_at')
            ->get();
    }
}
