<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Solicitud de reprogramación de una cita creada por el paciente y aprobada/rechazada por el especialista.
 */
class SolicitudReprogramacion extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_reprogramacion';

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_APROBADA  = 'aprobada';
    public const ESTADO_RECHAZADA = 'rechazada';

    protected $fillable = [
        'cita_id',
        'paciente_id',
        'especialista_id',
        'slot_id',
        'fecha_solicitada',
        'hora_solicitada',
        'motivo',
        'estado',
        'respuesta_especialista',
        'respondida_at',
    ];

    protected $casts = [
        'fecha_solicitada' => 'date',
        'respondida_at'    => 'datetime',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'paciente_id');
    }

    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }

    public function estaPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }
}
