<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionComprobante extends Model
{
    protected $table = 'configuracion_comprobantes';

    protected $fillable = [
        'tipo',
        'serie',
        'ultimo_correlativo',
    ];

    /**
     * Obtiene el siguiente correlativo y lo incrementa.
     */
    public static function getNextCorrelativo(string $tipo): array
    {
        $config = self::where('tipo', $tipo)->firstOrFail();
        $next = $config->ultimo_correlativo + 1;
        
        $config->update(['ultimo_correlativo' => $next]);

        return [
            'serie' => $config->serie,
            'correlativo' => $next
        ];
    }
}
