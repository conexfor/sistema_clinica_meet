<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de Rol
 * Almacena los roles disponibles del sistema
 */
class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'acciones',
    ];

    protected $casts = [
        'acciones' => 'array',
    ];

    /**
     * Relación con los usuarios
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }

    public function hasAction(string $action): bool
    {
        if ($this->nombre === 'admin') {
            return true;
        }

        return in_array($action, $this->acciones ?? [], true);
    }

    public function hasAnyAction(array $actions): bool
    {
        if ($this->nombre === 'admin') {
            return true;
        }

        return collect($actions)->contains(fn ($action) => $this->hasAction($action));
    }
}
