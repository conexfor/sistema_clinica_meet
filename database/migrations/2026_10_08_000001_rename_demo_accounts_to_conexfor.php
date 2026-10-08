<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renombra las cuentas de @sia.com a @conexfor.com conservando su id,
 * contraseña y relaciones (citas, especialista, paciente).
 */
return new class extends Migration
{
    private array $names = [
        'Administrador Sia' => 'Administrador Conexfor',
        'Recepción Sia' => 'Recepción Conexfor',
        'Admin Sia' => 'Admin Conexfor',
        'Recepcionista Sia' => 'Recepcionista Conexfor',
    ];

    public function up(): void
    {
        $this->rename('@sia.com', '@conexfor.com', $this->names);
    }

    public function down(): void
    {
        $this->rename('@conexfor.com', '@sia.com', array_flip($this->names));
    }

    private function rename(string $from, string $to, array $names): void
    {
        DB::table('usuarios')->where('email', 'like', '%' . $from)->orderBy('id')->each(function ($user) use ($from, $to) {
            $email = substr($user->email, 0, -strlen($from)) . $to;
            if (! DB::table('usuarios')->where('email', $email)->exists()) {
                DB::table('usuarios')->where('id', $user->id)->update(['email' => $email]);
            }
        });

        foreach ($names as $old => $new) {
            DB::table('usuarios')->where('nombre', $old)->update(['nombre' => $new]);
        }
    }
};
