<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo de Servicio
 * Representa un servicio ofrecido por la clínica (Psicología, Yoga, etc.).
 */
class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'duracion',
        'categoria',
        'categoria_id',
        'esta_activo',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
    ];

    /**
     * Alcance para servicios activos (por ahora todos).
     */
    public function scopeActive($query)
    {
        return $query->where('esta_activo', true);
    }

    /**
     * Categoría a la que pertenece el servicio.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Relación con los especialistas que ofrecen este servicio
     */
    public function especialistas(): BelongsToMany
    {
        return $this->belongsToMany(Especialista::class, 'especialista_servicio', 'servicio_id', 'especialista_id');
    }

    /**
     * Modalidades disponibles para este servicio (Virtual, Presencial, etc.).
     */
    public function modalidades(): BelongsToMany
    {
        return $this->belongsToMany(Modalidad::class, 'servicio_modalidad');
    }
}
