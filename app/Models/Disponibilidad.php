<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Disponibilidad
 * Representa los bloques horarios disponibles de un especialista.
 */
class Disponibilidad extends Model
{
    use HasFactory;

    protected $table = 'disponibilidades';

    protected $fillable = [
        'especialista_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'esta_reservada',
    ];

    protected $casts = [
        'fecha' => 'date',
        'esta_reservada' => 'boolean',
    ];

    /**
     * Relación con el especialista
     */
    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }
}
