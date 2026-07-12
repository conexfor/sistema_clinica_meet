<?php

namespace App\Listeners;

use App\Models\Auditoria;
use App\Services\Audit\AuditService;
use Illuminate\Auth\Events\Login;

/**
 * Registra en el log de auditoría cada inicio de sesión exitoso.
 */
class LogSuccessfulLogin
{
    public function __construct(
        protected AuditService $audit
    ) {}

    public function handle(Login $event): void
    {
        $usuario = $event->user;
        $rol = method_exists($usuario, 'rol') ? ($usuario->rol->nombre ?? 'usuario') : 'usuario';

        $this->audit->log(
            'login',
            ($usuario->nombre ?? 'Usuario') . ' inició sesión.',
            [
                'usuario_id' => $usuario->getAuthIdentifier(),
                'categoria'  => Auditoria::CAT_AUTH,
                'metadata'   => ['rol' => $rol, 'guard' => $event->guard],
            ]
        );
    }
}
