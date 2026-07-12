<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaEspecialista extends Model
{
    use HasFactory;

    protected $table = 'notas_especialistas';

    public const TIPO_GENERAL = 'general';
    public const TIPO_CRISIS  = 'crisis';

    protected $fillable = [
        'especialista_id',
        'paciente_id',
        'titulo',
        'contenido',
        'tipo',
    ];

    /**
     * Relación con el especialista que creó la nota.
     */
    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }

    /**
     * Paciente al que hace referencia la nota (opcional).
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'paciente_id');
    }

    public function esCrisis(): bool
    {
        return $this->tipo === self::TIPO_CRISIS;
    }
}
