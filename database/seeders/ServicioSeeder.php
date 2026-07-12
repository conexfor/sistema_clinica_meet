<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicios = [
            [
                'nombre' => 'Psicología General',
                'descripcion' => 'Sesión de psicoterapia individual para adultos.',
                'precio' => 50.00,
                'duracion' => '60',
                'categoria' => 'psicologia',
            ],
            [
                'nombre' => 'Yoga Restaurativo',
                'descripcion' => 'Clase de yoga enfocada en la relajación y estiramiento.',
                'precio' => 30.00,
                'duracion' => '60',
                'categoria' => 'yoga',
            ],
            [
                'nombre' => 'Terapia Cognitivo-Conductual',
                'descripcion' => 'Terapia especializada en el cambio de patrones de pensamiento.',
                'precio' => 60.00,
                'duracion' => '45',
                'categoria' => 'terapia',
            ],
            [
                'nombre' => 'Masaje Descontracturante',
                'descripcion' => 'Masaje profundo para liberar tensión muscular.',
                'precio' => 45.00,
                'duracion' => '45',
                'categoria' => 'masajes',
            ],
            [
                'nombre' => 'Masaje Relajante',
                'descripcion' => 'Masaje suave para reducir el estrés.',
                'precio' => 40.00,
                'duracion' => '60',
                'categoria' => 'masajes',
            ],
            [
                'nombre' => 'Meditación Guiada',
                'descripcion' => 'Sesión de meditación para calmar la mente.',
                'precio' => 20.00,
                'duracion' => '30',
                'categoria' => 'yoga',
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::updateOrCreate(['nombre' => $servicio['nombre']], $servicio);
        }
    }
}
