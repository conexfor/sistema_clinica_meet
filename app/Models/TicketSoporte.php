<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Ticket de Soporte
 * Incidencia reportada por un paciente o doctor para atención del admin.
 */
class TicketSoporte extends Model
{
    protected $table = 'tickets_soporte';

    public const ESTADO_ABIERTO    = 'abierto';
    public const ESTADO_EN_PROCESO = 'en_proceso';
    public const ESTADO_RESUELTO   = 'resuelto';

    public const CATEGORIAS = [
        'meet'   => 'Problemas con Google Meet',
        'cita'   => 'Problemas con una cita',
        'pago'   => 'Pagos y comprobantes',
        'cuenta' => 'Cuenta y acceso',
        'otro'   => 'Otro',
    ];

    protected $fillable = [
        'usuario_id',
        'rol',
        'categoria',
        'asunto',
        'mensaje',
        'estado',
        'respuesta',
        'respondido_por',
        'respondido_at',
    ];

    protected $casts = [
        'respondido_at' => 'datetime',
    ];

    public function scopeAbiertos($query)
    {
        return $query->where('estado', self::ESTADO_ABIERTO);
    }

    public function getCategoriaLabelAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? ucfirst($this->categoria);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function respondidoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'respondido_por');
    }
}
