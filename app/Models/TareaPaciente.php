<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tarea asignada por un especialista a un paciente (opcionalmente ligada a una cita).
 */
class TareaPaciente extends Model
{
    use HasFactory;

    protected $table = 'tareas_paciente';

    protected $fillable = [
        'especialista_id',
        'paciente_id',
        'cita_id',
        'titulo',
        'descripcion',
        'completada',
        'completada_at',
    ];

    protected $casts = [
        'completada'    => 'boolean',
        'completada_at' => 'datetime',
    ];

    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'paciente_id');
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}
