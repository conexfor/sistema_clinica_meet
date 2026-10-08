<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RoleActionController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $roles = Rol::orderBy('nombre')->get();
        $availableActions = config('role_actions.available_actions', []);
        $groups = config('role_actions.groups', []);
        $roleOptions = $roles->mapWithKeys(fn (Rol $rol) => [
            $rol->nombre => $this->getRoleLabel($rol->nombre),
        ])->toArray();

        $selectedRole = request('role') && $roles->pluck('nombre')->contains(request('role'))
            ? request('role')
            : $roles->first()?->nombre;

        $selectedRoleModel = $roles->firstWhere('nombre', $selectedRole) ?? $roles->first();

        return view('admin.roles.index', compact('roles', 'availableActions', 'groups', 'roleOptions', 'selectedRole', 'selectedRoleModel'));
    }

    private function getRoleLabel(string $roleName): string
    {
        return match ($roleName) {
            'admin' => 'Administrador',
            'doctor' => 'Doctor',
            'receptionist' => 'Recepcionista',
            'patient' => 'Paciente',
            default => ucfirst($roleName),
        };
    }

    public function update(Request $request, Rol $rol): \Illuminate\Http\RedirectResponse
    {
        $availableActions = array_keys(config('role_actions.available_actions', []));

        $validated = $request->validate([
            'acciones' => ['nullable', 'array'],
            'acciones.*' => ['in:' . implode(',', $availableActions)],
        ]);

        if ($rol->nombre === 'admin') {
            // El administrador siempre tiene todas las acciones disponibles.
            $rol->update(['acciones' => array_keys(config('role_actions.available_actions', []))]);
        } else {
            $rol->update([
                'acciones' => array_values($validated['acciones'] ?? []),
            ]);
        }

        return back()->with('success', 'Acciones del rol ' . ucfirst($rol->nombre) . ' actualizadas correctamente.');
    }
}
