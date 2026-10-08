<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Rol;

$configDefaults = config('role_actions.defaults');
$available = config('role_actions.available_actions');

echo "Starting role actions sync...\n";

foreach ($configDefaults as $rolName => $actions) {
    $rol = Rol::firstOrCreate(['nombre' => $rolName]);
    $existing = $rol->acciones ?? [];
    // Merge without duplicates, but keep existing custom actions
    $merged = array_values(array_unique(array_merge($existing, $actions)));
    $rol->acciones = $merged;
    $rol->save();
    echo "Rol '{$rolName}' synced. Actions count: " . count($merged) . "\n";
}

// Optionally ensure all admin has all available actions
$admin = Rol::where('nombre', 'admin')->first();
if ($admin) {
    $allKeys = array_keys($available);
    $admin->acciones = array_values(array_unique(array_merge($admin->acciones ?? [], $allKeys)));
    $admin->save();
    echo "Admin role updated with all available actions (total: " . count($admin->acciones) . ").\n";
}

echo "Sync complete.\n";
