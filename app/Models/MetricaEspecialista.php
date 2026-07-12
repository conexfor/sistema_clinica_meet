<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetricaEspecialista extends Model
{
    protected $table = 'metricas_especialistas';

    protected $fillable = [
        'especialista_id',
        'total_sesiones',
        'sesiones_completadas',
        'sesiones_canceladas',
        'sesiones_puntuales',
        'score_sistema',
    ];

    public function especialista()
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }
}
