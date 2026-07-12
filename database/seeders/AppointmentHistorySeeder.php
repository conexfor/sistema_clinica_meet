<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cita;
use App\Models\Usuario;
use App\Models\Psicologo;
use App\Models\Disponibilidad;
use Carbon\Carbon;

class AppointmentHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener un paciente (el primer usuario con rol patient o el primer usuario si no hay roles)
        $paciente = Usuario::whereHas('rol', function($q) {
            $q->where('nombre', Usuario::ROL_PATIENT);
        })->first() ?: Usuario::first();

        if (!$paciente) return;

        // 2. Obtener un psicólogo
        $psicologo = Psicologo::first();
        if (!$psicologo) return;

        // 3. Limpiar citas previas del paciente (opcional, pero ayuda a ver las nuevas)
        // Cita::where('paciente_id', $paciente->id)->delete();

        $citas = [
            // Citas Pasadas (Realizadas)
            [
                'fecha' => now()->subDays(7)->toDateString(),
                'hora' => '10:00:00',
                'estado' => Cita::STATUS_COMPLETED,
            ],
            [
                'fecha' => now()->subDays(3)->toDateString(),
                'hora' => '15:00:00',
                'estado' => Cita::STATUS_COMPLETED,
            ],
            // Cita de Hoy (Confirmada)
            [
                'fecha' => now()->toDateString(),
                'hora' => '17:00:00',
                'estado' => Cita::STATUS_CONFIRMED,
            ],
            // Citas Futuras
            [
                'fecha' => now()->addDays(2)->toDateString(),
                'hora' => '09:00:00',
                'estado' => Cita::STATUS_RESCHEDULED,
            ],
            [
                'fecha' => now()->addDays(5)->toDateString(),
                'hora' => '11:00:00',
                'estado' => Cita::STATUS_CONFIRMED,
            ],
        ];

        foreach ($citas as $data) {
            // Crear disponibilidad
            $disp = Disponibilidad::firstOrCreate([
                'psicologo_id' => $psicologo->id,
                'fecha' => $data['fecha'],
                'hora_inicio' => $data['hora'],
            ], [
                'hora_fin' => Carbon::parse($data['hora'])->addHour()->format('H:i:s'),
                'esta_reservada' => true
            ]);

            // Crear cita
            Cita::create([
                'psicologo_id' => $psicologo->id,
                'paciente_id' => $paciente->id,
                'disponibilidad_id' => $disp->id,
                'cantidad_sesiones' => 1,
                'estado' => $data['estado'],
                'meeting_url' => 'https://meet.google.com/abc-defg-hij',
            ]);
        }
    }
}
