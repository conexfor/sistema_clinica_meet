<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de Usuario
 * Representa a todas las personas en el sistema (Pacientes, Psicólogos, Admins, Recepcionistas)
 */
class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'password',
        'rol_id',
        'esta_activo',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
    ];

    // Constantes de Rol
    public const ROL_ADMIN = 'admin';
    public const ROL_DOCTOR = 'doctor';
    public const ROL_RECEPTIONIST = 'receptionist';
    public const ROL_PATIENT = 'patient';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'esta_activo' => 'boolean',
            'google_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Alcance para usuarios activos.
     */
    public function scopeActive($query)
    {
        return $query->where('esta_activo', true);
    }

    /**
     * Relación con el perfil de Especialista
     */
    public function especialista(): HasOne
    {
        return $this->hasOne(Especialista::class, 'usuario_id');
    }

    /**
     * Relación con el perfil de Paciente
     */
    public function paciente(): HasOne
    {
        return $this->hasOne(Paciente::class, 'usuario_id');
    }

    /**
     * Relación con las citas como paciente
     */
    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    /**
     * Relación con los registros de estado de ánimo
     */
    public function registrosAnimo(): HasMany
    {
        return $this->hasMany(RegistroAnimo::class, 'paciente_id');
    }

    // Helpers de Rol
    public function isAdmin(): bool { return $this->rol && $this->rol->nombre === self::ROL_ADMIN; }
    public function isDoctor(): bool { return $this->rol && $this->rol->nombre === self::ROL_DOCTOR; }
    public function isReceptionist(): bool { return $this->rol && $this->rol->nombre === self::ROL_RECEPTIONIST; }
    public function isPatient(): bool { return $this->rol && $this->rol->nombre === self::ROL_PATIENT; }

    /**
     * Relación con el rol del usuario
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
