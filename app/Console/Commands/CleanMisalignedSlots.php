<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Disponibilidad;
use App\Models\HorarioSemanal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Comando para limpiar slots de disponibilidad que no están alineados
 * con el horario de inicio del especialista.
 *
 * Ejemplo: si un especialista empieza a las 1:30 PM, los slots válidos son:
 *  1:30-2:30, 2:30-3:30, etc.
 * Si hay un slot 2:00-3:00 que no corresponde, se elimina.
 */
class CleanMisalignedSlots extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sia:clean-misaligned-slots {--dry-run : Solo mostrar qué se borraría, sin borrar}';

    /**
     * The console command description.
     */
    protected $description = 'Elimina slots de disponibilidad que no están alineados con el horario de inicio del especialista.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info($isDryRun ? '--- MODO SIMULACIÓN (--dry-run) ---' : '--- EJECUTANDO LIMPIEZA ---');

        // Obtener todos los HorariosSemanales para determinar minutos de inicio válidos por especialista
        $horariosSemanales = HorarioSemanal::all()->groupBy('especialista_id');

        // También incluir slots físicos (Disponibilidad con hora_inicio explícita)
        $allSlots = Disponibilidad::where('esta_reservada', false)
            ->orderBy('especialista_id')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $toDelete = collect();

        foreach ($allSlots as $slot) {
            $slotMinutes = (int) Carbon::parse($slot->hora_inicio)->format('i');

            // Obtener minutos de inicio válidos para este especialista
            $validMinutes = $this->getValidStartMinutes($slot->especialista_id, $slot->fecha, $horariosSemanales);

            // Si no hay horario semanal definido, asumimos que 00 minutos es válido
            if (empty($validMinutes)) {
                $validMinutes = [0];
            }

            // Si los minutos del slot no coinciden con ningún minuto de inicio válido
            if (!in_array($slotMinutes, $validMinutes)) {
                $toDelete->push($slot);
            }
        }

        if ($toDelete->isEmpty()) {
            $this->info('✅ No se encontraron slots mal alineados.');
            return self::SUCCESS;
        }

        $this->warn("Se encontraron {$toDelete->count()} slots mal alineados:");

        // Mostrar una tabla con los slots a eliminar
        $this->table(
            ['ID', 'Especialista ID', 'Fecha', 'Hora Inicio', 'Hora Fin'],
            $toDelete->map(fn($s) => [$s->id, $s->especialista_id, $s->fecha->toDateString(), $s->hora_inicio, $s->hora_fin])->toArray()
        );

        if ($isDryRun) {
            $this->comment('--- Simulación completada. Ningún registro fue eliminado. ---');
            return self::SUCCESS;
        }

        // Confirmar antes de borrar
        if (!$this->confirm("¿Deseas eliminar estos {$toDelete->count()} registros?", false)) {
            $this->info('Operación cancelada.');
            return self::SUCCESS;
        }

        // Eliminar
        $ids = $toDelete->pluck('id')->toArray();
        $deleted = Disponibilidad::whereIn('id', $ids)->delete();

        $this->info("✅ Se eliminaron {$deleted} slots correctamente.");

        return self::SUCCESS;
    }

    /**
     * Obtener los minutos de inicio válidos para un especialista en un día específico.
     */
    private function getValidStartMinutes(int $especialistaId, $fecha, $horariosSemanales): array
    {
        $dayOfWeek = Carbon::parse($fecha)->dayOfWeek;
        $schedules = $horariosSemanales->get($especialistaId, collect());

        $validMinutes = [];
        foreach ($schedules as $schedule) {
            // Verificar si el día de la semana coincide
            if ((int)$schedule->dia_semana === $dayOfWeek) {
                $startMinutes = (int) Carbon::parse($schedule->hora_inicio)->format('i');
                $validMinutes[] = $startMinutes;
            }
        }

        return array_unique($validMinutes);
    }
}
