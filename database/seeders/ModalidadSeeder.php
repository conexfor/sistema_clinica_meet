<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Modalidad::updateOrCreate(['id' => 1], [
            'nombre' => 'Virtual',
            'descripcion' => 'Sesión a través de videollamada (Google Meet).',
        ]);

        \App\Models\Modalidad::updateOrCreate(['id' => 2], [
            'nombre' => 'Presencial',
            'descripcion' => 'Sesión física en el consultorio de la clínica.',
        ]);
    }
}
