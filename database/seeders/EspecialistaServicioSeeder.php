<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Especialista;
use App\Models\HorarioSemanal;

/**
 * Registra un profesional (especialista) por cada categoría de servicio
 * y lo asocia a los servicios que ofrece mediante la tabla pivote
 * `especialista_servicio`. También crea horarios semanales para que los
 * profesionales aparezcan como disponibles en el flujo de reserva.
 *
 * Es idempotente: puede ejecutarse varias veces sin duplicar datos.
 */
class EspecialistaServicioSeeder extends Seeder
{
    /**
     * Definición de profesionales. Cada uno atiende una o más categorías;
     * se le asignan automáticamente todos los servicios activos de esas
     * categorías. Clave para todos: "password".
     */
    private array $profesionales = [
        [
            'nombre'       => 'Dra. Elena Salazar',
            'email'        => 'doctor@sia.com',
            'telefono'     => '999000003',
            'especialidad' => 'Psicología Clínica',
            'biografia'    => 'Especialista en ansiedad, estado de ánimo y acompañamiento terapéutico.',
            'foto'         => 'https://images.unsplash.com/photo-1559839734-2b71f1536783?q=80&w=200&auto=format&fit=crop',
            'categorias'   => ['psicologia', 'terapia'],
        ],
        [
            'nombre'       => 'Dr. Martín Ríos',
            'email'        => 'martin.rios@sia.com',
            'telefono'     => '999000010',
            'especialidad' => 'Terapia Cognitivo-Conductual',
            'biografia'    => 'Psicólogo especializado en manejo del estrés, fobias y reestructuración de pensamientos.',
            'foto'         => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?q=80&w=200&auto=format&fit=crop',
            'categorias'   => ['psicologia', 'terapia'],
        ],
        [
            'nombre'       => 'Lucía Fernández',
            'email'        => 'lucia.fernandez@sia.com',
            'telefono'     => '999000011',
            'especialidad' => 'Instructora de Yoga y Meditación',
            'biografia'    => 'Guía de yoga restaurativo y meditación con enfoque en relajación y bienestar.',
            'foto'         => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=200&auto=format&fit=crop',
            'categorias'   => ['yoga'],
        ],
        [
            'nombre'       => 'Carlos Mendoza',
            'email'        => 'carlos.mendoza@sia.com',
            'telefono'     => '999000012',
            'especialidad' => 'Masoterapeuta',
            'biografia'    => 'Especialista en masaje descontracturante y relajante para liberar tensión muscular.',
            'foto'         => 'https://images.unsplash.com/photo-1607990281513-2c110a25bd8c?q=80&w=200&auto=format&fit=crop',
            'categorias'   => ['masajes'],
        ],
    ];

    public function run(): void
    {
        // Garantizar que existan roles y servicios antes de asociar.
        $this->call(RoleSeeder::class);
        $this->call(ServicioSeeder::class);

        $doctorRolId = Rol::where('nombre', Usuario::ROL_DOCTOR)->value('id');

        foreach ($this->profesionales as $data) {
            // 1. Usuario base con rol doctor.
            $usuario = Usuario::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nombre'   => $data['nombre'],
                    'telefono' => $data['telefono'],
                    'password' => Hash::make('password'),
                    'rol_id'   => $doctorRolId,
                ]
            );

            // 2. Perfil de especialista.
            $especialista = Especialista::updateOrCreate(
                ['usuario_id' => $usuario->id],
                [
                    'especialidad' => $data['especialidad'],
                    'biografia'    => $data['biografia'],
                    'foto'         => $data['foto'],
                ]
            );

            // 3. Asociar todos los servicios activos de sus categorías (pivote).
            $servicioIds = Servicio::active()
                ->whereIn('categoria', $data['categorias'])
                ->pluck('id');

            $especialista->servicios()->syncWithoutDetaching($servicioIds);

            // 4. Horario semanal (Lun-Sáb) para que aparezca como disponible.
            $this->crearHorarioSemanal($especialista);
        }

        $this->reportarServiciosSinProfesional();
    }

    /**
     * Crea disponibilidad recurrente de lunes a sábado, 09:00 - 18:00.
     */
    private function crearHorarioSemanal(Especialista $especialista): void
    {
        foreach (range(1, 6) as $dia) { // 1 = Lunes ... 6 = Sábado
            HorarioSemanal::updateOrCreate(
                [
                    'especialista_id' => $especialista->id,
                    'dia_semana'      => $dia,
                    'hora_inicio'     => '09:00:00',
                ],
                [
                    'hora_fin'    => '18:00:00',
                    'esta_activo' => true,
                ]
            );
        }
    }

    /**
     * Avisa por consola si algún servicio activo quedó sin profesional asignado.
     */
    private function reportarServiciosSinProfesional(): void
    {
        $sinProfesional = Servicio::active()->doesntHave('especialistas')->pluck('nombre');

        if ($sinProfesional->isNotEmpty()) {
            $this->command->warn('Servicios sin profesional asignado: ' . $sinProfesional->implode(', '));
            return;
        }

        $this->command->info('Todos los servicios activos tienen al menos un profesional asignado.');
    }
}
