<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingEspecialista extends Model
{
    protected $table = 'ratings_especialistas';

    protected $fillable = [
        'especialista_id',
        'paciente_id',
        'cita_id',
        'rating',
        'comentario',
    ];

    public function especialista()
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Usuario::class, 'paciente_id');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}
