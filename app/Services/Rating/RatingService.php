<?php

namespace App\Services\Rating;

use App\Models\Especialista;
use App\Models\MetricaEspecialista;
use App\Models\RatingEspecialista;
use App\Models\Cita;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para manejar la lógica del Rating Híbrido.
 * rating_final = (rating_pacientes * 0.7) + (score_sistema * 0.3)
 */
class RatingService
{
    /**
     * Recalcula todas las métricas de un especialista.
     */
    public function recalculateMetrics(int $especialistaId): void
    {
        $citas = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->get(['citas.*', 'disponibilidades.hora_inicio', 'disponibilidades.fecha']);

        $total = $citas->count();
        $completadas = $citas->where('estado', Cita::STATUS_COMPLETED)->count();
        $canceladasEspecialista = $citas->where('estado', Cita::STATUS_CANCELLED)
            ->where('cancelado_por_especialista', true)
            ->count();
        
        $puntuales = 0;
        foreach ($citas->whereNotNull('atendido_at') as $cita) {
            // $cita->fecha proviene del JOIN con disponibilidades (string, sin cast a Carbon).
            $scheduledStart = \Carbon\Carbon::parse(\Carbon\Carbon::parse($cita->fecha)->toDateString() . ' ' . $cita->hora_inicio);
            // Se considera puntual si inició máximo 5 minutos después o en cualquier momento antes
            if ($cita->atendido_at->lte($scheduledStart->copy()->addMinutes(5))) {
                $puntuales++;
            }
        }

        $metricas = MetricaEspecialista::updateOrCreate(
            ['especialista_id' => $especialistaId],
            [
                'total_sesiones' => $total,
                'sesiones_completadas' => $completadas,
                'sesiones_canceladas' => $canceladasEspecialista,
                'sesiones_puntuales' => $puntuales,
            ]
        );

        $this->updateSystemScore($metricas);
        $this->updateFinalRating($especialistaId);
    }

    /**
     * Calcula el score del sistema (0-5).
     * score_sistema = (puntualidad * 0.4 + (1 - cancelaciones) * 0.3 + sesiones_completadas * 0.3) * 5
     */
    private function updateSystemScore(MetricaEspecialista $metricas): void
    {
        $total = $metricas->total_sesiones ?: 1;
        
        $puntualidad = $metricas->sesiones_puntuales / $total;
        $cancelacionesRatio = $metricas->sesiones_canceladas / $total;
        $completadasRatio = $metricas->sesiones_completadas / $total;

        $score = ($puntualidad * 0.4) + ((1 - $cancelacionesRatio) * 0.3) + ($completadasRatio * 0.3);
        
        // Convertir a escala 0-5
        $metricas->update(['score_sistema' => $score * 5]);
    }

    /**
     * Actualiza el rating final combinando pacientes y sistema.
     */
    public function updateFinalRating(int $especialistaId): void
    {
        $especialista = Especialista::findOrFail($especialistaId);
        
        // Promedio de pacientes
        $avgPatientRating = RatingEspecialista::where('especialista_id', $especialistaId)->avg('rating') ?? 0;
        $totalReviews = RatingEspecialista::where('especialista_id', $especialistaId)->count();

        // Score del sistema
        $systemScore = $especialista->metricas->score_sistema ?? 0;

        // Si no hay reviews aún, no podemos ponderar el 70% de algo que no existe.
        // Pero el requerimiento dice 70/30. Si no hay reviews, quizás solo usamos el sistema o un base.
        if ($totalReviews === 0) {
            $finalRating = $systemScore; // O podrías definir un valor base
        } else {
            $finalRating = ($avgPatientRating * 0.7) + ($systemScore * 0.3);
        }

        $especialista->update([
            'rating_promedio' => $avgPatientRating,
            'rating_final' => $finalRating,
            'total_reviews' => $totalReviews,
        ]);
    }

    /**
     * Registra un nuevo rating de paciente.
     */
    public function storePatientRating(array $data): void
    {
        DB::transaction(function () use ($data) {
            RatingEspecialista::updateOrCreate(
                ['cita_id' => $data['cita_id']],
                [
                    'especialista_id' => $data['especialista_id'],
                    'paciente_id' => $data['paciente_id'],
                    'rating' => $data['rating'],
                    'comentario' => $data['comentario'] ?? null,
                ]
            );

            $this->updateFinalRating($data['especialista_id']);
        });
    }
}
