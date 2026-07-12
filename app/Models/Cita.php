<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Cita
 * Representa una sesión reservada entre un paciente y un especialista.
 */
class Cita extends Model
{
    use HasFactory;

    public const STATUS_RESERVADA = 'confirmada'; // Equivale a Reservada
    public const STATUS_ATTENTION = 'en_atencion';
    public const STATUS_COMPLETED = 'realizada';
    public const STATUS_RESCHEDULED = 'reprogramada';
    public const STATUS_CANCELLED = 'cancelada';
    public const STATUS_NO_SHOW = 'no_asistio'; // Paciente no llegó ni avisó

    protected $table = 'citas';

    protected $fillable = [
        'especialista_id',
        'servicio_id',
        'paciente_id',
        'disponibilidad_id',
        'cantidad_sesiones',
        'tipo_consulta',
        'duracion_minutos',
        'estado',
        'motivo_cancelacion',
        'cita_original_id',
        'modalidad_id',
        'meeting_url',
        'costo',
        'total',
        'calificacion',
        'comentario_paciente',
        'resumen_sesion',
        'resumen_compartido',
        'animo_inicial',
        'animo_final',
        'atendido_at',
        'finalizado_at',
        'cancelado_por_especialista',
        'metodo_pago',
        'tipo_comprobante',
        'documento_serie',
        'documento_correlativo',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'atendido_at' => 'datetime',
        'finalizado_at' => 'datetime',
        'cancelado_por_especialista' => 'boolean',
        'resumen_compartido' => 'boolean',
    ];

    /**
     * Tareas asignadas al paciente en esta cita.
     */
    public function tareas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TareaPaciente::class, 'cita_id');
    }

    /**
     * Solicitudes de reprogramación asociadas a esta cita.
     */
    public function solicitudesReprogramacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SolicitudReprogramacion::class, 'cita_id');
    }

    /**
     * Recordatorio enviado para esta cita (si existe).
     */
    public function recordatorio(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RecordatorioCita::class, 'cita_id');
    }

    /**
     * Relación con la calificación detallada (Híbrida)
     */
    public function ratingDetallado(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RatingEspecialista::class, 'cita_id');
    }

    /**
     * Relación con el especialista
     */
    public function especialista(): BelongsTo
    {
        return $this->belongsTo(Especialista::class, 'especialista_id');
    }

    /**
     * Relación con la modalidad (Virtual, Presencial, etc.).
     */
    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidad_id');
    }

    /**
     * Relación con el servicio reservado.
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Relación con el paciente
     */
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'paciente_id');
    }

    /**
     * Relación con la disponibilidad horaria específica
     */
    public function disponibilidad(): BelongsTo
    {
        return $this->belongsTo(Disponibilidad::class, 'disponibilidad_id');
    }

    /**
     * Relación con la cita original (historial)
     */
    public function citaOriginal(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_original_id');
    }

    /**
     * Relaciones con citas derivadas (reprogramaciones posteriores)
     */
    public function citasDerivadas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Cita::class, 'cita_original_id');
    }

    /**
     * Accessor para obtener la hora de fin de la cita
     * Basado en la hora de inicio y la duración total.
     */
    public function getHoraFinAttribute(): string
    {
        if (!$this->disponibilidad) {
            return '--:--';
        }

        // Se calcula el fin sumando la duración real a la hora de inicio
        $duracionTotal = ($this->duracion_minutos ?? 60) * ($this->cantidad_sesiones ?? 1);
        return \Carbon\Carbon::parse($this->disponibilidad->hora_inicio)
            ->addMinutes($duracionTotal)
            ->format('h:i A');
    }

    /**
     * Obtener el objeto Carbon del inicio de la cita
     */
    public function getStartAtAttribute(): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse($this->disponibilidad->fecha->toDateString() . ' ' . $this->disponibilidad->hora_inicio);
    }

    /**
     * Obtener el objeto Carbon del fin de la cita
     */
    public function getEndAtAttribute(): \Carbon\Carbon
    {
        $duracionTotal = ($this->duracion_minutos ?? 60) * ($this->cantidad_sesiones ?? 1);
        return $this->start_at->copy()->addMinutes($duracionTotal);
    }

    /**
     * Quién canceló la cita ('Especialista' | 'Paciente'), o null si no está cancelada.
     */
    public function getCanceladoPorAttribute(): ?string
    {
        if ($this->estado !== self::STATUS_CANCELLED) {
            return null;
        }

        return $this->cancelado_por_especialista ? 'Especialista' : 'Paciente';
    }

    /**
     * Determina si el botón de unirse debe estar activo (30min antes hasta el fin)
     */
    public function getCanJoinAttribute(): bool
    {
        $now = now();
        $joinWindowStart = $this->start_at->copy()->subMinutes(30);
        $joinWindowEnd = $this->end_at;

        return $now->between($joinWindowStart, $joinWindowEnd);
    }

    /**
     * Obtener configuracion del badge de estado
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->estado) {
            self::STATUS_RESERVADA => [
                'label' => 'Reservada',
                'color' => 'indigo',
                'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            ],
            self::STATUS_ATTENTION => [
                'label' => 'En atención',
                'color' => 'rose',
                'icon' => '<svg class="w-7 h-7 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>'
            ],
            self::STATUS_COMPLETED => [
                'label' => 'Realizada',
                'color' => 'emerald',
                'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
            ],
            self::STATUS_RESCHEDULED => [
                'label' => 'Reprogramada',
                'color' => 'amber',
                'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
            ],
            self::STATUS_CANCELLED => [
                'label' => 'Cancelada',
                'color' => 'slate',
                'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>'
            ],
            self::STATUS_NO_SHOW => [
                'label' => 'No asistió',
                'color' => 'sand',
                'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
            ],
            default => [
                'label' => ucfirst($this->estado),
                'color' => 'slate',
                'icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            ],
        };
    }
}
