<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Lista de Espera
 * Representa una solicitud de cita que no encontró horario disponible.
 */
class ListaEspera extends Model
{
    protected $table = 'lista_espera';

    public const ESTADO_PENDIENTE  = 'pendiente';
    public const ESTADO_CONTACTADO = 'contactado';
    public const ESTADO_RESUELTO   = 'resuelto';

    protected $fillable = [
        'paciente_id',
        'nombre',
        'telefono',
        'email',
        'servicio_id',
        'especialista_id',
        'modalidad_id',
        'notas',
        'estado',
    ];

    /**
     * Nombre a mostrar: el del paciente registrado o el texto libre.
     */
    public function getNombreMostradoAttribute(): string
    {
        return $this->paciente->nombre ?? $this->nombre ?? 'Sin nombre';
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'paciente_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }
}
