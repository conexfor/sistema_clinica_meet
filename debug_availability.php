<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Booking\MedicalBookingService;
use Illuminate\Http\Request;

$service = app(MedicalBookingService::class);
$espId = 1; // Testing with ID 1
$fecha = date('Y-m-d');
$sessions = 1;
$duracion = 60;

try {
    $slots = $service->getAvailableSlots($espId, $fecha, 'standard', $sessions, $duracion);
    echo "Slots found: " . count($slots) . "\n";
    print_r($slots);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
