<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo de Especialista
 * Representa el perfil profesional de un especialista/doctor.
 */
class Especialista extends Model
{
    use HasFactory;

    protected $table = 'especialistas';

    protected $fillable = [
        'usuario_id',
        'especialidad',
        'biografia',
        'foto',
        'rating_promedio',
        'rating_final',
        'total_reviews',
    ];

    /**
     * Accessor para la URL de la foto del especialista.
     * Retorna la foto cargada o un avatar basado en el nombre si no existe.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return str_starts_with($this->foto, 'http') 
                ? $this->foto 
                : asset('storage/' . $this->foto);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->usuario?->nombre ?? 'E') . '&background=6366f1&color=fff';
    }

    /**
     * Relación con sus métricas automáticas
     */
    public function metricas(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MetricaEspecialista::class, 'especialista_id');
    }

    /**
     * Relación con sus valoraciones detalladas
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(RatingEspecialista::class, 'especialista_id');
    }

    /**
     * Relación con el usuario base
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con sus disponibilidades horarias
     */
    public function disponibilidades(): HasMany
    {
        return $this->hasMany(Disponibilidad::class, 'especialista_id');
    }

    /**
     * Relación con sus citas asignadas
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'especialista_id');
    }

    /**
     * Relación con sus horarios semanales fijos
     */
    public function horariosSemanales(): HasMany
    {
        return $this->hasMany(HorarioSemanal::class, 'especialista_id');
    }

    /**
     * Relación con los servicios que ofrece
     */
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'especialista_servicio', 'especialista_id', 'servicio_id');
    }
}
