<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notificación en la aplicación (campanita) para un usuario.
 */
class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    public const TIPO_RECORDATORIO   = 'recordatorio';
    public const TIPO_REPROGRAMACION = 'reprogramacion';
    public const TIPO_TAREA          = 'tarea';
    public const TIPO_CANCELACION    = 'cancelacion';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'titulo',
        'mensaje',
        'url',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * URL relativa segura para el enlace de la notificación.
     * Convierte URLs absolutas (con dominio) en rutas relativas para no
     * perder la sesión ni salir del origen actual, y asegura el ancla de la
     * sección correspondiente.
     */
    public function getHrefAttribute(): string
    {
        $url = $this->url ?: '/';

        if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) {
            $parts = parse_url($url);
            $url = ($parts['path'] ?? '/') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
        }

        // Las notificaciones de reprogramación deben llevar a "Tus solicitudes de reprogramación".
        if ($this->tipo === self::TIPO_REPROGRAMACION && !str_contains($url, '#')) {
            $url .= '#solicitudes';
        }

        return $url;
    }

    /**
     * Helper para crear una notificación rápidamente.
     */
    public static function crear(int $usuarioId, string $tipo, string $titulo, string $mensaje, ?string $url = null): self
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'tipo'       => $tipo,
            'titulo'     => $titulo,
            'mensaje'    => $mensaje,
            'url'        => $url,
        ]);
    }
}
