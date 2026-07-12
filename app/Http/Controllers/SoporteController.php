<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\TicketSoporte;
use App\Models\Usuario;
use Illuminate\Http\Request;

/**
 * Soporte / incidencias: los pacientes y doctores reportan problemas (store);
 * el admin los gestiona desde la bandeja (index/respond/updateEstado).
 */
class SoporteController extends Controller
{
    /**
     * Registrar una incidencia (paciente o doctor autenticado).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria' => 'required|in:meet,cita,pago,cuenta,otro',
            'asunto'    => 'required|string|max:150',
            'mensaje'   => 'required|string|max:2000',
        ]);

        $user = $request->user();

        $ticket = TicketSoporte::create([
            'usuario_id' => $user->id,
            'rol'        => $user->rol->nombre ?? null,
            'categoria'  => $validated['categoria'],
            'asunto'     => $validated['asunto'],
            'mensaje'    => $validated['mensaje'],
            'estado'     => TicketSoporte::ESTADO_ABIERTO,
        ]);

        $this->notificarAdmins($ticket, $user);

        return back()->with('success', 'Tu reporte fue enviado. El equipo lo revisará pronto.');
    }

    /**
     * Notificar a todos los administradores que hay un reporte por resolver.
     */
    private function notificarAdmins(TicketSoporte $ticket, Usuario $autor): void
    {
        $adminIds = Usuario::whereHas('rol', fn ($q) => $q->where('nombre', Usuario::ROL_ADMIN))
            ->pluck('id');

        $url = route('admin.soporte.index', [], false);

        foreach ($adminIds as $adminId) {
            Notificacion::crear(
                $adminId,
                'general',
                'Nuevo reporte de soporte',
                ($autor->nombre ?? 'Un usuario') . ' reportó: "' . $ticket->asunto . '".',
                $url
            );
        }
    }

    /**
     * Bandeja de incidencias (admin).
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $estado = $request->get('estado');

        $tickets = TicketSoporte::with(['usuario', 'respondidoPor'])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $conteos = [
            'abierto'    => TicketSoporte::where('estado', TicketSoporte::ESTADO_ABIERTO)->count(),
            'en_proceso' => TicketSoporte::where('estado', TicketSoporte::ESTADO_EN_PROCESO)->count(),
            'resuelto'   => TicketSoporte::where('estado', TicketSoporte::ESTADO_RESUELTO)->count(),
        ];

        return view('admin.soporte.index', compact('tickets', 'conteos', 'estado'));
    }

    /**
     * Responder un ticket y marcarlo como resuelto; notifica al autor.
     */
    public function respond(Request $request, int $id)
    {
        $validated = $request->validate([
            'respuesta' => 'required|string|max:2000',
        ]);

        $ticket = TicketSoporte::findOrFail($id);
        $ticket->update([
            'respuesta'      => $validated['respuesta'],
            'estado'         => TicketSoporte::ESTADO_RESUELTO,
            'respondido_por' => $request->user()->id,
            'respondido_at'  => now(),
        ]);

        Notificacion::crear(
            $ticket->usuario_id,
            'general',
            'Respuesta a tu reporte de soporte',
            'Respondimos tu incidencia "' . $ticket->asunto . '".',
            null
        );

        return back()->with('success', 'Respuesta enviada y ticket resuelto.');
    }

    /**
     * Cambiar el estado de un ticket sin responder (ej. marcar en proceso).
     */
    public function updateEstado(Request $request, int $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:abierto,en_proceso,resuelto',
        ]);

        TicketSoporte::findOrFail($id)->update(['estado' => $validated['estado']]);

        return back()->with('success', 'Estado del ticket actualizado.');
    }
}
