<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de RegistroAnimo
 * Representa un registro diario del estado emocional de un paciente.
 */
class RegistroAnimo extends Model
{
    use HasFactory;

    protected $table = 'registros_animo';

    protected $fillable = [
        'paciente_id',
        'estado_animo',
        'valor_animo',
        'nota',
    ];

    /**
     * Relación con el paciente
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'paciente_id');
    }
}
