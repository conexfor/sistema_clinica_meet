<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Rol;
use App\Models\Usuario;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles por defecto si no existen
        $roles = ['admin', 'doctor', 'receptionist', 'patient'];
        $roleMap = [];
        foreach ($roles as $nombre) {
            $rol = Rol::firstOrCreate(['nombre' => $nombre]);
            $roleMap[$nombre] = $rol->id;
        }

        // Migrar los roles de la tabla usuarios
        // Ya que la columna rol fue reemplazada en algunas partes, verificaremos si podemos hacer update directo.
        
        $usuarios = DB::table('usuarios')->get();
        foreach ($usuarios as $user) {
            if (isset($user->rol) && isset($roleMap[$user->rol])) {
                DB::table('usuarios')->where('id', $user->id)->update(['rol_id' => $roleMap[$user->rol]]);
            } elseif (isset($user->role) && isset($roleMap[$user->role])) {
                DB::table('usuarios')->where('id', $user->id)->update(['rol_id' => $roleMap[$user->role]]);
            } elseif (empty($user->rol_id)) {
                // Asignar paciente por defecto
                DB::table('usuarios')->where('id', $user->id)->update(['rol_id' => $roleMap['patient']]);
            }
        }
    }
}
