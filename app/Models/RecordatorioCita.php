<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de un recordatorio de cita enviado (log de notificaciones).
 */
class RecordatorioCita extends Model
{
    use HasFactory;

    protected $table = 'recordatorios_cita';

    protected $fillable = [
        'cita_id',
        'paciente_id',
        'especialista_id',
        'fecha_cita',
        'canal',
        'enviado_at',
    ];

    protected $casts = [
        'fecha_cita' => 'date',
        'enviado_at' => 'datetime',
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
}
