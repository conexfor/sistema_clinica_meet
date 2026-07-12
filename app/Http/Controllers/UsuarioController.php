<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Usuario;
use App\Services\Audit\AuditService;
use Illuminate\Http\Request;

/**
 * Gestión de usuarios (rol admin): listado de todas las cuentas y
 * activación/desactivación. Un usuario desactivado no puede iniciar sesión.
 */
class UsuarioController extends Controller
{
    public function __construct(
        protected AuditService $audit
    ) {}

    /**
     * Listado de todos los usuarios, con filtro opcional por rol.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $rol = $request->get('rol');

        $usuarios = Usuario::with('rol')
            ->when($rol, fn ($q) => $q->whereHas('rol', fn ($r) => $r->where('nombre', $rol)))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $roles = [
            Usuario::ROL_ADMIN        => 'Administradores',
            Usuario::ROL_DOCTOR       => 'Doctores',
            Usuario::ROL_RECEPTIONIST => 'Recepcionistas',
            Usuario::ROL_PATIENT      => 'Pacientes',
        ];

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'rol'));
    }

    /**
     * Activar/desactivar un usuario. El admin no puede desactivarse a sí mismo.
     */
    public function toggleEstado(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);

        // Un admin no puede cambiar su propio estado (evita auto-bloqueo).
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'No puedes cambiar el estado de tu propia cuenta.');
        }

        $usuario->update(['esta_activo' => ! $usuario->esta_activo]);

        $accion = $usuario->esta_activo ? 'activó' : 'desactivó';
        $this->audit->log(
            'usuario.estado',
            'Se ' . $accion . ' la cuenta de ' . ($usuario->nombre ?? 'usuario') . ' (' . ($usuario->rol->nombre ?? 'sin rol') . ').',
            [
                'categoria' => Auditoria::CAT_SISTEMA,
                'metadata'  => ['usuario_id' => $usuario->id, 'esta_activo' => $usuario->esta_activo],
            ]
        );

        return back()->with('success', 'Usuario ' . ($usuario->esta_activo ? 'activado' : 'desactivado') . ' correctamente.');
    }
}
