<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Psicologo;
use App\Models\Rol;
use App\Models\Disponibilidad;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar que los roles existan
        $this->call(RoleSeeder::class);

        // Obtener IDs de Roles
        $adminId = Rol::where('nombre', 'admin')->first()->id;
        $receptionistId = Rol::where('nombre', 'receptionist')->first()->id;
        $doctorId = Rol::where('nombre', 'doctor')->first()->id;
        $patientId = Rol::where('nombre', 'patient')->first()->id;

        // --- ADMIN ---
        Usuario::updateOrCreate(
            ['email' => 'admin@clinica.com'],
            [
                'nombre' => 'Admin Sia',
                'password' => Hash::make('password'),
                'rol_id' => $adminId,
            ]
        );

        // --- RECEPCIONISTA ---
        Usuario::updateOrCreate(
            ['email' => 'recepcion@clinica.com'],
            [
                'nombre' => 'Recepcionista Sia',
                'password' => Hash::make('password'),
                'rol_id' => $receptionistId,
            ]
        );

        // --- PSICÓLOGO 1 ---
        $doc1User = Usuario::updateOrCreate(
            ['email' => 'nicolas@clinica.com'],
            [
                'nombre' => 'Dr. Nicolas',
                'password' => Hash::make('password'),
                'rol_id' => $doctorId,
            ]
        );

        $psicologo1 = Psicologo::updateOrCreate(
            ['usuario_id' => $doc1User->id],
            [
                'especialidad' => 'Psicología Clínica',
                'biografia' => 'Especialista en ansiedad y depresión con más de 10 años de experiencia.',
                'foto_perfil' => 'https://images.unsplash.com/photo-1594824436998-058a2313e6a3?q=80&w=200&auto=format&fit=crop',
            ]
        );

        // --- PSICÓLOGO 2 ---
        $doc2User = Usuario::updateOrCreate(
            ['email' => 'elena@clinica.com'],
            [
                'nombre' => 'Dra. Elena',
                'password' => Hash::make('password'),
                'rol_id' => $doctorId,
            ]
        );

        $psicologo2 = Psicologo::updateOrCreate(
            ['usuario_id' => $doc2User->id],
            [
                'especialidad' => 'Terapia Cognitivo-Conductual',
                'biografia' => 'Experta en manejo de estrés y fobias.',
                'foto_perfil' => 'https://images.unsplash.com/photo-1559839734-2b71f1536783?q=80&w=200&auto=format&fit=crop',
            ]
        );

        // --- PACIENTE ---
        Usuario::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'nombre' => 'John Doe',
                'password' => Hash::make('password'),
                'rol_id' => $patientId,
            ]
        );

        // --- DISPONIBILIDADES ---
        // Generar algunas disponibilidades para los próximos 5 días si no existen
        for ($i = 1; $i <= 5; $i++) {
            $fecha = now()->addDays($i)->format('Y-m-d');
            
            // Psicólogo 1
            Disponibilidad::firstOrCreate([
                'psicologo_id' => $psicologo1->id,
                'fecha' => $fecha,
                'hora_inicio' => '13:00',
            ], [
                'hora_fin' => '14:00',
                'esta_reservada' => false,
            ]);

            // Psicólogo 2
            Disponibilidad::firstOrCreate([
                'psicologo_id' => $psicologo2->id,
                'fecha' => $fecha,
                'hora_inicio' => '10:00',
            ], [
                'hora_fin' => '11:00',
                'esta_reservada' => false,
            ]);
        }
    }
}
