<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Rol;
use App\Models\Usuario;

$rol = Rol::where('nombre', 'receptionist')->first();
echo "ROL_JSON:" . json_encode($rol?->toArray()) . PHP_EOL;

$user = null;
if ($rol) {
    $user = Usuario::where('rol_id', $rol->id)->first();
}

echo "USER_JSON:" . json_encode($user?->only(['id','nombre','email'])) . PHP_EOL;
echo "USER_ROLE_ACCIONES:" . json_encode($user?->rol?->acciones ?? null) . PHP_EOL;

// Also show whether user->hasAction for common keys
$keys = ['book_appointments','view_reports','manage_services','manage_specialists','manage_users'];
$map = [];
foreach ($keys as $k) {
    $map[$k] = $user ? $user->hasAction($k) : null;
}
echo "USER_EFFECTIVE: " . json_encode($map) . PHP_EOL;
