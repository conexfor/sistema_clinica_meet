<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Rol;

$available = array_keys(config('role_actions.available_actions'));
$availableMap = array_flip($available);

echo "Pruning role actions to available keys...\n";
$changed = 0;
$totalRoles = 0;
foreach (Rol::all() as $rol) {
    $totalRoles++;
    $existing = $rol->acciones ?? [];
    $filtered = array_values(array_intersect($existing, $available));
    if ($rol->nombre === 'admin') {
        // Ensure admin has all available actions
        $filtered = $available;
    }
    sort($filtered);
    $existingSorted = $existing;
    sort($existingSorted);
    if ($filtered !== $existingSorted) {
        $rol->acciones = $filtered;
        $rol->save();
        $changed++;
        echo "- Rol '{$rol->nombre}' actualizado. Acciones ahora: " . count($filtered) . "\n";
    } else {
        echo "- Rol '{$rol->nombre}' sin cambios (" . count($filtered) . " acciones)\n";
    }
}

echo "\nResumen: {$totalRoles} roles procesados, {$changed} modificados.\n";
echo "Prune complete.\n";
