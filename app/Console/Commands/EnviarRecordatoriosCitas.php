<?php

namespace App\Console\Commands;

use App\Services\Recordatorio\RecordatorioService;
use Illuminate\Console\Command;
use Carbon\Carbon;

/**
 * Envía los recordatorios de las citas del día siguiente.
 * Pensado para ejecutarse a diario vía scheduler (ver bootstrap/app.php).
 */
class EnviarRecordatoriosCitas extends Command
{
    protected $signature = 'citas:recordatorios {--fecha= : Fecha objetivo (Y-m-d); por defecto mañana}';

    protected $description = 'Envía recordatorios de las citas del día siguiente y los registra.';

    public function handle(RecordatorioService $service): int
    {
        $fecha = $this->option('fecha') ?: Carbon::tomorrow()->toDateString();

        $this->info("Enviando recordatorios para el {$fecha}...");
        $enviados = $service->enviarParaFecha($fecha);
        $this->info("Recordatorios enviados: {$enviados}");

        return self::SUCCESS;
    }
}
