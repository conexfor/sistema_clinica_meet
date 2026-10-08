<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Especialista;
use App\Models\Paciente;

/**
 * Crea un usuario por cada rol del sistema.
 * Clave para todos: "password".
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que los roles existan (admin, doctor, receptionist, patient)
        $this->call(RoleSeeder::class);

        $roles = Rol::pluck('id', 'nombre'); // ['admin' => 1, 'doctor' => 2, ...]

        // --- ADMINISTRADOR ---
        Usuario::updateOrCreate(
            ['email' => 'admin@conexfor.com'],
            [
                'nombre'   => 'Administrador Conexfor',
                'telefono' => '999000001',
                'password' => Hash::make('password'),
                'rol_id'   => $roles[Usuario::ROL_ADMIN] ?? null,
            ]
        );

        // --- RECEPCIONISTA ---
        Usuario::updateOrCreate(
            ['email' => 'recepcion@conexfor.com'],
            [
                'nombre'   => 'Recepción Conexfor',
                'telefono' => '999000002',
                'password' => Hash::make('password'),
                'rol_id'   => $roles[Usuario::ROL_RECEPTIONIST] ?? null,
            ]
        );

        // --- DOCTOR / ESPECIALISTA ---
        $doctor = Usuario::updateOrCreate(
            ['email' => 'doctor@conexfor.com'],
            [
                'nombre'   => 'Dra. Elena Salazar',
                'telefono' => '999000003',
                'password' => Hash::make('password'),
                'rol_id'   => $roles[Usuario::ROL_DOCTOR] ?? null,
            ]
        );

        // Perfil profesional (necesario para el panel del doctor)
        Especialista::updateOrCreate(
            ['usuario_id' => $doctor->id],
            [
                'especialidad' => 'Psicología Clínica',
                'biografia'    => 'Especialista en ansiedad, estado de ánimo y acompañamiento terapéutico.',
            ]
        );

        // --- PACIENTE ---
        $paciente = Usuario::updateOrCreate(
            ['email' => 'paciente@conexfor.com'],
            [
                'nombre'   => 'Juan Pérez',
                'telefono' => '999000004',
                'password' => Hash::make('password'),
                'rol_id'   => $roles[Usuario::ROL_PATIENT] ?? null,
            ]
        );

        // Perfil de paciente (necesario para el portal del paciente)
        Paciente::updateOrCreate(
            ['usuario_id' => $paciente->id],
            [
                'distrito' => 'Miraflores',
            ]
        );

        $this->command->info('Usuarios por rol creados (clave: password):');
        $this->command->table(
            ['Rol', 'Email', 'Clave'],
            [
                ['Administrador', 'admin@conexfor.com', 'password'],
                ['Recepcionista', 'recepcion@conexfor.com', 'password'],
                ['Doctor',        'doctor@conexfor.com', 'password'],
                ['Paciente',      'paciente@conexfor.com', 'password'],
            ]
        );
    }
}
