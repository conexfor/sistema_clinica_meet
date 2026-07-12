<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioSemanal extends Model
{
    use HasFactory;

    protected $table = 'horarios_semanales';

    protected $fillable = [
        'especialista_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'esta_activo',
    ];

    /**
     * Relación con el especialista.
     */
    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }

    /**
     * Atributo para el nombre del día.
     */
    public function getNombreDiaAttribute(): string
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];

        return $dias[$this->dia_semana] ?? 'Desconocido';
    }
}
