<?php

namespace App\Services\Audit;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditService
 * Punto único para registrar acciones sensibles en el log de auditoría.
 */
class AuditService
{
    /**
     * Registrar una entrada de auditoría.
     *
     * @param  string       $accion       Identificador corto (login, servicio.precio, ...).
     * @param  string       $descripcion  Texto legible para el admin.
     * @param  array        $opts         categoria, usuario_id, metadata.
     */
    public function log(string $accion, string $descripcion, array $opts = []): Auditoria
    {
        return Auditoria::create([
            'usuario_id'  => $opts['usuario_id'] ?? Auth::id(),
            'accion'      => $accion,
            'categoria'   => $opts['categoria'] ?? Auditoria::CAT_SISTEMA,
            'descripcion' => $descripcion,
            'ip'          => Request::ip(),
            'metadata'    => $opts['metadata'] ?? null,
        ]);
    }
}
