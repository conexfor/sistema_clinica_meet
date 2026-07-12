<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Especialista;
use App\Models\Usuario;
use App\Models\Cita;
use App\Models\Modalidad;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AdminController extends Controller
{
    public function __construct(
        protected \App\Services\Admin\AdminService $adminService
    ) {}

    /**
     * Muestra el panel administrativo con estadísticas globales.
     */
    public function dashboard(Request $request): \Illuminate\View\View
    {
        $period = $request->get('period', 'month');
        $stats = $this->adminService->getDashboardStats($period);

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Marcar todas las notificaciones del admin como leídas.
     */
    public function marcarNotificacionesLeidas(Request $request): \Illuminate\Http\RedirectResponse
    {
        \App\Models\Notificacion::where('usuario_id', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return back();
    }

    /**
     * Listado de pacientes.
     */
    public function pacientesIndex(): \Illuminate\View\View
    {
        $pacientes = Usuario::whereHas('rol', function ($q) {
            $q->where('nombre', 'patient');
        })->with('paciente')->paginate(10);

        return view('admin.pacientes.index', compact('pacientes'));
    }

    /**
     * Listado de todas las citas (Reporte).
     */
    public function citasIndex(): \Illuminate\View\View
    {
        $citas = Cita::with(['paciente', 'especialista.usuario', 'disponibilidad', 'modalidad'])
            ->latest()
            ->paginate(15);

        return view('admin.citas.index', compact('citas'));
    }

    /**
     * Listado de especialistas.
     */
    public function especialistasIndex(): \Illuminate\View\View
    {
        $especialistas = Especialista::with('usuario', 'servicios')->get();
        return view('admin.especialistas.index', compact('especialistas'));
    }

    /**
     * Formulario de creación de especialista.
     */
    public function createEspecialista(): \Illuminate\View\View
    {
        $servicios = \App\Models\Servicio::active()->get();
        return view('admin.especialistas.create', compact('servicios'));
    }

    /**
     * Guardar nuevo especialista.
     */
    public function storeEspecialista(Request $request, \App\Services\Especialista\EspecialistaService $service)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|min:8|confirmed',
            'especialidad' => 'required|string',
            'biografia' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('especialistas', 'public');
            $validated['foto'] = $path;
        }

        $service->createEspecialista($validated);

        return redirect()->route('admin.especialistas.index')->with('success', 'Especialista creado correctamente.');
    }

    /**
     * Formulario de edición de especialista.
     */
    public function editEspecialista(int $id): \Illuminate\View\View
    {
        $especialista = Especialista::with('usuario', 'servicios')->findOrFail($id);
        $servicios = \App\Models\Servicio::active()->get();
        return view('admin.especialistas.edit', compact('especialista', 'servicios'));
    }

    /**
     * Actualizar especialista.
     */
    public function updateEspecialista(Request $request, int $id, \App\Services\Especialista\EspecialistaService $service)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email,' . Especialista::findOrFail($id)->usuario_id,
            'password' => 'nullable|min:8|confirmed',
            'especialidad' => 'required|string',
            'biografia' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'servicios' => 'nullable|array',
            'servicios.*' => 'exists:servicios,id',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('especialistas', 'public');
            $validated['foto'] = $path;
        }

        $service->updateEspecialista($id, $validated);

        return redirect()->route('admin.especialistas.index')->with('success', 'Especialista actualizado correctamente.');
    }

    // --- Gestión de Servicios ---

    /**
     * Listado de servicios.
     */
    public function serviciosIndex(\App\Services\Servicio\ServicioService $service)
    {
        $servicios = $service->getAllServicios();
        return view('admin.servicios.index', compact('servicios'));
    }

    /**
     * Formulario de creación de servicio.
     */
    public function serviciosCreate()
    {
        $categorias = \App\Models\Categoria::active()->orderBy('nombre')->get();
        $duraciones = [30, 45, 60];
        $modalidades = Modalidad::all();
        return view('admin.servicios.create', compact('categorias', 'duraciones', 'modalidades'));
    }

    /**
     * Guardar nuevo servicio.
     */
    public function serviciosStore(StoreServicioRequest $request, \App\Services\Servicio\ServicioService $service)
    {
        $service->createServicio($request->validated());

        return redirect()->route('admin.servicios.index')->with('success', 'Servicio creado correctamente.');
    }

    /**
     * Formulario de edición de servicio.
     */
    public function serviciosEdit(int $id)
    {
        $servicio = \App\Models\Servicio::with('modalidades')->findOrFail($id);
        $categorias = \App\Models\Categoria::active()->orderBy('nombre')->get();
        $duraciones = [30, 45, 60];
        $modalidades = Modalidad::all();
        return view('admin.servicios.edit', compact('servicio', 'categorias', 'duraciones', 'modalidades'));
    }

    /**
     * Actualizar servicio.
     */
    public function serviciosUpdate(UpdateServicioRequest $request, int $id, \App\Services\Servicio\ServicioService $service)
    {
        $service->updateServicio($id, $request->validated());

        return redirect()->route('admin.servicios.index')->with('success', 'Servicio actualizado correctamente.');
    }

    /**
     * Alternar estado del servicio.
     */
    public function serviciosToggleStatus(int $id, \App\Services\Servicio\ServicioService $service)
    {
        $service->toggleStatus($id);
        return redirect()->route('admin.servicios.index')->with('success', 'Estado del servicio actualizado.');
    }

    /**
     * Eliminar servicio.
     */
    public function serviciosDestroy(int $id, \App\Services\Servicio\ServicioService $service)
    {
        $service->deleteServicio($id);
        return redirect()->route('admin.servicios.index')->with('success', 'Servicio eliminado correctamente.');
    }
}
