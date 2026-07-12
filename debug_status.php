<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Cita;

echo "--- CITAS EN LA DB ---\n";
$citas = Cita::with(['paciente', 'disponibilidad'])->orderBy('id', 'desc')->limit(10)->get();

foreach ($citas as $cita) {
    echo "ID: {$cita->id} | Paciente: {$cita->paciente->nombre} | Estado: {$cita->estado} | Fecha: {$cita->disponibilidad->fecha->toDateString()} | Slot: {$cita->disponibilidad->id}\n";
}
