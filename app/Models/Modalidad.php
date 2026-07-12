<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modalidad extends Model
{
    use HasFactory;

    protected $table = 'modalidades';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * IDs constantes de modalidades por defecto.
     */
    public const VIRTUAL = 1;
    public const PRESENCIAL = 2;

    /**
     * Servicios que ofrecen esta modalidad.
     */
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'servicio_modalidad');
    }

    /**
     * Citas realizadas bajo esta modalidad.
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
