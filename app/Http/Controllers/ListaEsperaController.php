<?php

namespace App\Http\Controllers;

use App\Models\Especialista;
use App\Models\ListaEspera;
use App\Models\Modalidad;
use App\Models\Servicio;
use Illuminate\Http\Request;

/**
 * Gestión de la lista de espera (rol admin).
 */
class ListaEsperaController extends Controller
{
    /**
     * Listado con filtro por estado + formulario de alta manual.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $estado = $request->get('estado');

        $solicitudes = ListaEspera::with(['paciente', 'servicio', 'especialista.usuario', 'modalidad'])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $conteos = [
            'pendiente'  => ListaEspera::where('estado', ListaEspera::ESTADO_PENDIENTE)->count(),
            'contactado' => ListaEspera::where('estado', ListaEspera::ESTADO_CONTACTADO)->count(),
            'resuelto'   => ListaEspera::where('estado', ListaEspera::ESTADO_RESUELTO)->count(),
        ];

        $servicios = Servicio::active()->orderBy('nombre')->get();
        $especialistas = Especialista::with('usuario')->get();
        $modalidades = Modalidad::all();

        return view('admin.lista-espera.index', compact('solicitudes', 'conteos', 'estado', 'servicios', 'especialistas', 'modalidades'));
    }

    /**
     * Alta manual de una solicitud (paciente que llamó por teléfono, etc.).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:255',
            'telefono'        => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255',
            'servicio_id'     => 'nullable|exists:servicios,id',
            'especialista_id' => 'nullable|exists:especialistas,id',
            'modalidad_id'    => 'nullable|exists:modalidades,id',
            'notas'           => 'nullable|string|max:500',
        ]);

        $validated['estado'] = ListaEspera::ESTADO_PENDIENTE;
        ListaEspera::create($validated);

        return redirect()->route('admin.lista-espera.index')->with('success', 'Solicitud agregada a la lista de espera.');
    }

    /**
     * Cambiar el estado de una solicitud (pendiente/contactado/resuelto).
     */
    public function updateEstado(Request $request, int $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,contactado,resuelto',
        ]);

        ListaEspera::findOrFail($id)->update(['estado' => $validated['estado']]);

        return back()->with('success', 'Estado de la solicitud actualizado.');
    }

    /**
     * Eliminar una solicitud de la lista.
     */
    public function destroy(int $id)
    {
        ListaEspera::findOrFail($id)->delete();

        return back()->with('success', 'Solicitud eliminada de la lista de espera.');
    }
}
