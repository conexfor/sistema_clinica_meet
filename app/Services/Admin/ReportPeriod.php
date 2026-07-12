<?php

namespace App\Services\Admin;

use Carbon\Carbon;

/**
 * ReportPeriod
 * Resuelve un identificador de período (today, week, month, ...) en un rango de
 * fechas concreto y su rango anterior equivalente (para cálculos de crecimiento).
 * Reutilizado por todos los servicios de reporte del admin.
 */
class ReportPeriod
{
    public Carbon $start;
    public Carbon $end;
    public Carbon $prevStart;
    public Carbon $prevEnd;
    public string $key;
    public string $label;

    /**
     * Opciones disponibles para el filtro de período (clave => etiqueta).
     */
    public const OPTIONS = [
        'today'      => 'Hoy',
        'week'       => 'Esta semana',
        'month'      => 'Este mes',
        'last_month' => 'Mes anterior',
        '3_months'   => 'Últimos 3 meses',
        '6_months'   => 'Últimos 6 meses',
        'year'       => 'Año actual',
    ];

    public function __construct(string $period)
    {
        $now = Carbon::now();
        $this->key = array_key_exists($period, self::OPTIONS) ? $period : 'month';
        $this->label = self::OPTIONS[$this->key];

        $this->start = match ($this->key) {
            'today'      => $now->copy()->startOfDay(),
            'week'       => $now->copy()->startOfWeek(),
            'month'      => $now->copy()->startOfMonth(),
            'last_month' => $now->copy()->subMonthNoOverflow()->startOfMonth(),
            '3_months'   => $now->copy()->subMonthsNoOverflow(3)->startOfDay(),
            '6_months'   => $now->copy()->subMonthsNoOverflow(6)->startOfDay(),
            'year'       => $now->copy()->startOfYear(),
            default      => $now->copy()->startOfMonth(),
        };

        $this->end = match ($this->key) {
            'last_month' => $now->copy()->subMonthNoOverflow()->endOfMonth(),
            default      => $now->copy()->endOfDay(),
        };

        // Rango anterior equivalente (misma duración, desplazado hacia atrás).
        $lengthInDays = $this->start->diffInDays($this->end) + 1;
        $this->prevEnd = $this->start->copy()->subDay()->endOfDay();
        $this->prevStart = $this->prevEnd->copy()->subDays($lengthInDays - 1)->startOfDay();
    }

    public static function make(string $period): self
    {
        return new self($period);
    }
}
