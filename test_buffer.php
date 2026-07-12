<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Collection;

class TestService extends \App\Services\Booking\MedicalBookingService {
    public function testSim() {
        $fecha = Carbon::today()->toDateString();
        $segment = ['start' => Carbon::parse($fecha . ' 21:00:00'), 'end' => Carbon::parse($fecha . ' 23:00:00')];
        $especialista = new \App\Models\Especialista();
        $especialista->id = 1;
        
        $slots = $this->generateSlotsInSegment($segment, 30, $fecha, -20, $especialista, 'standard');
        
        foreach ($slots as $slot) {
            echo "Slot generated: {$slot['start']} - {$slot['end']}\n";
        }
    }
}

$t = new TestService();
$t->testSim();
