<?php

namespace App\Observers;

use App\Models\Auditoria;
use App\Models\Usuario;
use App\Services\Audit\AuditService;

/**
 * Registra en auditoría las bajas de usuarios (especialmente pacientes).
 */
class UsuarioObserver
{
    public function __construct(
        protected AuditService $audit
    ) {}

    public function deleting(Usuario $usuario): void
    {
        $rol = $usuario->rol->nombre ?? 'usuario';

        $this->audit->log(
            'usuario.baja',
            'Se eliminó al ' . $rol . ' "' . ($usuario->nombre ?? 'sin nombre') . '" (' . ($usuario->email ?? '—') . ').',
            [
                'categoria' => Auditoria::CAT_PACIENTES,
                'metadata'  => ['usuario_eliminado_id' => $usuario->id, 'rol' => $rol],
            ]
        );
    }
}
