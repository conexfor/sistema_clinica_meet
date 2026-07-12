<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServicioModalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vincular TODAS las modalidades disponibles a cada servicio,
        // para que el cliente pueda elegir modalidad al reservar.
        $modalidadIds = \App\Models\Modalidad::pluck('id')->all();

        if (empty($modalidadIds)) {
            return;
        }

        \App\Models\Servicio::all()->each(function ($servicio) use ($modalidadIds) {
            // syncWithoutDetaching no duplica filas existentes en el pivote.
            $servicio->modalidades()->syncWithoutDetaching($modalidadIds);
        });
    }
}
