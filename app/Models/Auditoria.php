<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Auditoría
 * Cada fila registra una acción sensible realizada en el sistema.
 */
class Auditoria extends Model
{
    protected $table = 'auditorias';

    // Categorías de auditoría.
    public const CAT_AUTH      = 'auth';
    public const CAT_FINANZAS  = 'finanzas';
    public const CAT_PACIENTES = 'pacientes';
    public const CAT_SISTEMA   = 'sistema';

    protected $fillable = [
        'usuario_id',
        'accion',
        'categoria',
        'descripcion',
        'ip',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
