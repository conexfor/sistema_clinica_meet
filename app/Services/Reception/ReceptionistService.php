<?php

namespace App\Services\Reception;

use App\Models\Cita;
use App\Models\Usuario;
use App\Models\Disponibilidad;
use App\Models\Especialista;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ReceptionistService
{
    /**
     * Obtiene las estadísticas para el dashboard de Recepción,
     * adaptadas a la estructura analítica premium (DentalPro Replica).
     */
    public function getDashboardStats(string $period = 'month'): array
    {
        $now = now();

        // --- 1. Calcular KPIs Principales (datos reales del sistema) ---
        // Citas Agendadas Hoy (registradas hoy en el sistema)
        $agendadasHoy = Cita::whereDate('created_at', $now->toDateString())->count();

        // Cobros Realizados (Mes)
        $cobrosMes = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->where('citas.estado', Cita::STATUS_COMPLETED)
            ->sum('citas.total');

        // Total Citas del Mes (Conteo)
        $totalCitasMes = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->count();

        // Citas Realizadas (Mes)
        $realizadasMes = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->where('citas.estado', Cita::STATUS_COMPLETED)
            ->count();

        // Citas Canceladas (Mes)
        $canceladasMes = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->where('citas.estado', Cita::STATUS_CANCELLED)
            ->count();

        // Doctores con disponibilidad libre hoy
        $doctoresDisp = Especialista::whereHas('disponibilidades', function($q) use ($now) {
            $q->where('fecha', $now->toDateString())->where('esta_reservada', false);
        })->count();

        // --- 2. Chart: Flujo de Reservas por Día (datos reales) ---
        $startOfWeek = now()->startOfWeek(); // Lunes
        $endOfWeek = now()->endOfWeek()->subDay(); // Sábado

        $reservasSemana = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->whereBetween('disponibilidades.fecha', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->select('disponibilidades.fecha', DB::raw('count(*) as total'))
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
            ->select('citas.metodo_pago', DB::raw('count(*) as total'))
            ->groupBy('citas.metodo_pago')
            ->pluck('total', 'citas.metodo_pago')
            ->toArray();

        return [
            'current_period' => $period,
            'kpis' => [
                'agendadas_hoy'   => $agendadasHoy,
                'realizadas_mes'  => $realizadasMes,
                'total_citas_mes' => $totalCitasMes,
                'doctores_disp'   => $doctoresDisp,
                'canceladas_mes'  => $canceladasMes,
            ],
            'charts' => [
                'flujo_labels' => array_keys($flujoData),
                'flujo_values' => array_values($flujoData),
                'metodos_labels' => array_keys($metodosData),
                'metodos_values' => array_values($metodosData),
            ],
        ];
    }

    /**
     * Buscar un usuario por email o nombre, o crear uno nuevo si no existe.
     * En este sistema, los pacientes son Usuarios con rol_id = 1.
     */
    public function searchOrCreatePatient(array $data): Usuario
    {
        $email = $data['email'] ?? null;
        
        if ($email) {
            $usuario = Usuario::where('email', $email)->first();
            if ($usuario) {
                return $usuario;
            }
        }

        // Si no existe, lo creamos
        return DB::transaction(function () use ($data) {
            $usuario = Usuario::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'password' => bcrypt('pass123'), // Password genérica para acceso posterior
                'rol_id' => 4, // Paciente
            ]);

            // Crear el perfil de paciente
            $usuario->paciente()->create([
                // Ya no guardamos telefono/numero aquí
            ]);

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
            $query->whereHas('especialista.disponibilidades', function($q) {
                $q->whereDate('fecha', now()->toDateString())
                  ->where('esta_reservada', false);
            });
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
