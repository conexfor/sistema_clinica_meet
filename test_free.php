<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Collection;

class TestService extends \App\Services\Booking\MedicalBookingService {
    public function testSim() {
        // Shift is 09:00 to 18:00
        $shift = ['start' => '09:00:00', 'end' => '18:00:00'];
        
        $app1 = new \App\Models\Cita();
        $disp = new \App\Models\Disponibilidad();
        $disp->hora_inicio = '09:30:00';
        $app1->setRelation('disponibilidad', $disp);
        $app1->duracion_minutos = 45;
        $app1->cantidad_sesiones = 1;

        $appointments = new Collection([$app1]);

        $freeSegments = $this->calculateFreeSegments($shift, $appointments);
        print_r($freeSegments);
    }
}

$t = new TestService();
$t->testSim();
