<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cita;
use App\Models\Disponibilidad;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TestService extends \App\Services\Booking\MedicalBookingService {
    public function testSim() {
        // Shift is 09:00 to 13:00
        $shift = ['start' => '09:00:00', 'end' => '13:00:00'];
        
        // Appointment: 09:30 to 10:15
        $disp = new Disponibilidad();
        $disp->hora_inicio = '09:30:00';
        $disp->hora_fin = '10:15:00';
        
        $app1 = new Cita();
        $app1->setRelation('disponibilidad', $disp);
        $app1->duracion_minutos = 45;
        $app1->cantidad_sesiones = 1;

        $appointments = new Collection([$app1]);

        $freeSegments = $this->calculateFreeSegments($shift, $appointments);
        print_r($freeSegments);

        echo "Generating slots for 60m (1h)...\n";
        $fecha = '2026-03-18';
        $especialista = new \App\Models\Especialista();
        $especialista->id = 1;
        
        foreach ($freeSegments as $segment) {
            echo "Generando para segmento: " . $segment['start']->format('H:i') . " a " . $segment['end']->format('H:i') . "\n";
            $slots = $this->generateSlotsInSegment($segment, 60, $fecha, -20, $especialista, 'standard');
            foreach ($slots as $slot) {
                echo "  Slot: {$slot['start']} - {$slot['end']}\n";
            }
        }
    }
}

$tester = new TestService();
$tester->testSim();
echo "\nDONE\n";
