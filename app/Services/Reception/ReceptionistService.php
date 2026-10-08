<?php

namespace App\Services\Reception;

use App\Models\Cita;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Disponibilidad;
use App\Models\Especialista;
use App\Services\Booking\MedicalBookingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ReceptionistService
{
    public function __construct(protected MedicalBookingService $medicalBookingService) {}

    /**
     * Obtiene las estadísticas para el dashboard de Recepción,
     * adaptadas a la estructura analítica premium (DentalPro Replica).
     */
    public function getDashboardStats(string $period = 'month'): array
    {
        $now = now();

        // --- 1. Calcular KPIs Principales (datos reales del sistema) ---
        // Citas Agendadas Hoy (sesiones nuevas registradas hoy)
        $agendadasHoy = (int) Cita::whereDate('created_at', $now->toDateString())
            ->selectRaw('COALESCE(SUM(COALESCE(cantidad_sesiones, 1)), 0) as total')
            ->value('total');

        // Cobros Realizados (Mes)
        $cobrosMes = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->whereNotNull('citas.metodo_pago')
            ->sum('citas.total');

        // Total Citas del Mes (sesiones reales)
        $totalCitasMes = (int) Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->selectRaw('COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->value('total');

        // Citas Realizadas (Mes) por sesiones completadas
        $realizadasMes = (int) Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->where('citas.estado', Cita::STATUS_COMPLETED)
            ->selectRaw('COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->value('total');

        // Citas Canceladas (Mes) por sesiones canceladas
        $canceladasMes = (int) Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->where('citas.estado', Cita::STATUS_CANCELLED)
            ->selectRaw('COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->value('total');

        // Especialistas con disponibilidad libre hoy.
        // Usamos la misma lógica de slots que el workflow de agendar cita,
        // para que el KPI refleje recursos reales y no solo registros de
        // disponibilidades físicas sin considerar la jornada semanal.
        $especialistasDisp = Especialista::with('servicios')->get()->filter(function ($especialista) use ($now) {
            return $this->especialistaHasAvailabilityToday($especialista, $now->toDateString());
        })->count();

        // --- 2. Chart: Flujo de Reservas por Día (datos reales) ---
        $startOfWeek = now()->startOfWeek(); // Lunes
        $endOfWeek = now()->endOfWeek()->subDay(); // Sábado

        $reservasSemana = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->selectRaw('disponibilidades.fecha, COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->groupBy('disponibilidades.fecha')
            ->get()
            ->pluck('total', 'fecha');

        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $flujoData = [];

        foreach ($diasSemana as $index => $nombreDia) {
            $fechaDia = $startOfWeek->copy()->addDays($index)->toDateString();
            $flujoData[$nombreDia] = $reservasSemana[$fechaDia] ?? 0;
        }

        // --- 3. Chart: Distribución por método de pago (datos reales del mes) ---
        $metodosData = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->whereNotNull('citas.metodo_pago')
            ->selectRaw('citas.metodo_pago, COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->groupBy('citas.metodo_pago')
            ->pluck('total', 'citas.metodo_pago')
            ->toArray();

        return [
            'current_period' => $period,
            'kpis' => [
                'agendadas_hoy'      => $agendadasHoy,
                'realizadas_mes'     => $realizadasMes,
                'total_citas_mes'    => $totalCitasMes,
                'especialistas_disp' => $especialistasDisp,
                'canceladas_mes'     => $canceladasMes,
                'cobros_mes'         => $cobrosMes,
            ],
            'charts' => [
                'flujo_labels' => array_keys($flujoData),
                'flujo_values' => array_values($flujoData),
                'metodos_labels' => array_keys($metodosData),
                'metodos_values' => array_values($metodosData),
            ],
        ];
    }

    private function especialistaHasAvailabilityToday($especialista, string $fecha): bool
    {
        $now = now();
        $duraciones = $especialista->servicios
            ->map(fn($servicio) => $servicio->duracion ?? 60)
            ->unique()
            ->values();

        if ($duraciones->isEmpty()) {
            return false;
        }

        foreach ($duraciones as $duracion) {
            $slots = $this->medicalBookingService->getAvailableSlots(
                $especialista->id,
                $fecha,
                'standard',
                1,
                $duracion
            );

            $flatSlots = collect($slots['morning'])
                ->concat($slots['afternoon'])
                ->concat($slots['evening']);

            if ($flatSlots->contains(function ($slot) use ($now, $fecha) {
                return \Carbon\Carbon::parse("{$fecha} {$slot['start']}")->greaterThanOrEqualTo($now);
            })) {
                return true;
            }
        }

        return false;
    }

    private function getAvailableSpecialistIdsOnDate(string $fecha)
    {
        return Especialista::with('servicios')->get()
            ->filter(fn($especialista) => $this->especialistaHasAvailabilityToday($especialista, $fecha))
            ->pluck('id');
    }

    /**
     * Buscar un usuario por email o nombre, o crear uno nuevo si no existe.
     * En este sistema, los pacientes son Usuarios con rol_id = 1.
     */
    public function searchOrCreatePatient(array $data): Usuario
    {
        $email = $data['email'] ?? null;
        $telefono = $data['telefono'] ?? null;
        $patientType = $data['patient_type'] ?? 'simple';

        if ($email) {
            $usuario = Usuario::where('email', $email)->first();
            if ($usuario) {
                return $usuario;
            }
        }

        if ($telefono) {
            $usuario = Usuario::where('telefono', $telefono)->first();
            if ($usuario) {
                return $usuario;
            }
        }

        // Si no existe, lo creamos
        return DB::transaction(function () use ($data, $patientType) {
            $patientRoleId = Rol::where('nombre', Usuario::ROL_PATIENT)->value('id') ?? 4;
            $usuario = Usuario::create([
                'nombre' => $data['nombre'],
                'email' => $patientType === 'account' ? $data['email'] : null,
                'telefono' => $data['telefono'] ?? null,
                'password' => $patientType === 'account' ? bcrypt($data['password']) : null,
                'rol_id' => $patientRoleId,
            ]);

            $usuario->paciente()->create([]);

            return $usuario;
        });
    }

    /**
     * Crear una reserva desde recepción con costo editable.
     */
    public function bookAppointment(array $data): Cita
    {
        return DB::transaction(function () use ($data) {
            // El total se calcula: costo unitario * cantidad de sesiones
            $total = ($data['costo'] ?? 0) * ($data['cantidad_sesiones'] ?? 1);

            $cita = Cita::create([
                'especialista_id'   => $data['especialista_id'],
                'servicio_id'       => $data['servicio_id'] ?? null,
                'paciente_id'       => $data['paciente_id'],
                'disponibilidad_id' => $data['disponibilidad_id'],
                'cantidad_sesiones' => $data['cantidad_sesiones'] ?? 1,
                'costo'             => $data['costo'],
                'total'             => $total,
                'estado'            => Cita::STATUS_RESERVADA,
                'modalidad_id'      => $data['modalidad_id'] ?? 1, // Default presencial si no viene
                'tipo_consulta'     => $data['tipo_consulta'] ?? 'Presencial',
                'duracion_minutos'  => 60, // Default 60min
            ]);

            // Marcar la disponibilidad como reservada
            Disponibilidad::where('id', $data['disponibilidad_id'])->update(['esta_reservada' => true]);

            return $cita;
        });
    }

    /**
     * Obtener reportes paginados con filtros.
     */
    public function getReports(array $filters): LengthAwarePaginator
    {
        $query = Cita::with(['paciente', 'especialista.usuario', 'disponibilidad'])
            ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->select('citas.*');

        if (!empty($filters['especialista_id'])) {
            $query->where('citas.especialista_id', $filters['especialista_id']);
        }

        if (!empty($filters['estado'])) {
            $query->where('citas.estado', $filters['estado']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('disponibilidades.fecha', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('disponibilidades.fecha', '<=', $filters['fecha_hasta']);
        }

        if (!empty($filters['disponibles_hoy'])) {
            $availableEspecialistaIds = $this->getAvailableSpecialistIdsOnDate(now()->toDateString());
            if ($availableEspecialistaIds->isEmpty()) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('citas.especialista_id', $availableEspecialistaIds);
            }
        }

        // Orden de la agenda por prioridad respecto al momento actual. La cita se compara como
        // datetime completo (fecha + hora_inicio) para no fallar al cruzar medianoche:
        //   0 = hoy y por delante de ahora (las próximas de hoy)  -> ascendente
        //   1 = hasta 20 min de atraso (arrancaron recién)        -> menor atraso primero
        //   2 = otros días futuros                                -> ascendente
        //   3 = pasadas (hoy con +20 min de atraso, y días previos) -> más reciente primero
        $dt    = 'TIMESTAMP(disponibilidades.fecha, disponibilidades.hora_inicio)';
        $hoy   = now()->toDateString();
        $ahora = now()->format('Y-m-d H:i:s');
        $grace = now()->subMinutes(20)->format('Y-m-d H:i:s');

        return $query
            ->orderByRaw("CASE
                    WHEN disponibilidades.fecha = ? AND {$dt} >= ? THEN 0
                    WHEN {$dt} >= ? AND {$dt} < ? THEN 1
                    WHEN disponibilidades.fecha > ? THEN 2
                    ELSE 3
                END", [$hoy, $ahora, $grace, $ahora, $hoy])
            // Grupos ascendentes (0 hoy-próximas y 2 futuras): datetime ascendente (más próximo primero).
            ->orderByRaw("CASE WHEN (disponibilidades.fecha = ? AND {$dt} >= ?) OR disponibilidades.fecha > ? THEN {$dt} END ASC", [$hoy, $ahora, $hoy])
            // Grupos descendentes (1 atrasadas -> menor atraso primero; 3 pasadas -> más reciente primero).
            ->orderByRaw("{$dt} DESC")
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Calcular la sumatoria total de los reportes filtrados.
     */
    public function getGeneralTotal(array $filters): float
    {
        $query = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id');

        if (!empty($filters['especialista_id'])) {
            $query->where('citas.especialista_id', $filters['especialista_id']);
        }

        if (!empty($filters['estado'])) {
            $query->where('citas.estado', $filters['estado']);
        }

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('disponibilidades.fecha', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('disponibilidades.fecha', '<=', $filters['fecha_hasta']);
        }

        return (float) $query->sum('total');
    }
}
