<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de Categoría
 * Clasifica los servicios de la clínica (Psicología, Yoga, Terapia, etc.).
 */
class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'esta_activo',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
    ];

    /**
     * Alcance para categorías activas.
     */
    public function scopeActive($query)
    {
        return $query->where('esta_activo', true);
    }

    /**
     * Servicios que pertenecen a esta categoría.
     */
    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class, 'categoria_id');
    }
}
