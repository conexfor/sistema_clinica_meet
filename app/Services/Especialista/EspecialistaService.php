<?php

namespace App\Services\Especialista;

use App\Models\Disponibilidad;
use App\Models\Cita;
use App\Models\Usuario;
use App\Models\Especialista;
use App\Models\NotaEspecialista;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EspecialistaService
{
    /**
     * Obtiene las estadísticas para el dashboard del Doctor,
     * adaptadas exactamente a la estructura analítica premium (DentalPro Replica).
     */
    public function getDashboardStats(int $especialistaId, string $period = 'month'): array
    {
        // Reutiliza el mismo cálculo de métricas reales para el dashboard del especialista.
        $metrics = $this->getRealMetrics($especialistaId);

        return [
            'current_period' => $period,
            'kpis' => [
                'citas_hoy'      => $metrics['kpis']['citas_hoy'],
                'citas_growth'   => 0.0,
                'pacientes_tot'  => $metrics['kpis']['pacientes_tot'],
                'pacientes_gwth' => 0.0,
                'ingresos_mes'   => 0,
                'ingresos_gwth'  => 0.0,
                'horas_mes'      => 0,
                'horas_gwth'     => 0.0,
                'rating_avg'     => null,
            ],
            'charts' => $metrics['charts'],
        ];
    }

    /**
     * Obtener el perfil del especialista para un usuario específico.
     */
    public function getEspecialistaProfile(int $usuarioId)
    {
        return Usuario::findOrFail($usuarioId)->especialista;
    }

    /**
     * Obtener citas próximas para un especialista.
     */
    public function getUpcomingAppointments(int $especialistaId)
    {
        /**
         * Obtiene las citas confirmadas ordenadas cronológicamente.
         * Se une a la tabla de disponibilidades para permitir el filtrado y ordenamiento por fecha/hora.
         */
        return Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->whereIn('citas.estado', [
                Cita::STATUS_RESERVADA,
                Cita::STATUS_ATTENTION
            ])
            ->where('disponibilidades.fecha', '>=', now()->toDateString())
            ->with(['paciente', 'disponibilidad'])
            ->select('citas.*')
            ->orderBy('disponibilidades.fecha', 'asc')
            ->orderBy('disponibilidades.hora_inicio', 'asc')
            ->get();
    }

    /**
     * Crear múltiples bloques de disponibilidad para un especialista.
     */
    public function createAvailabilitySlots(int $especialistaId, array $bloques)
    {
        return DB::transaction(function () use ($especialistaId, $bloques) {
            $creados = [];
            foreach ($bloques as $bloque) {
                // Evitar duplicados si ya existe un bloque igual
                $exists = Disponibilidad::where([
                    'especialista_id' => $especialistaId,
                    'fecha' => $bloque['fecha'],
                    'hora_inicio' => $bloque['hora_inicio'],
                    'hora_fin' => $bloque['hora_fin'],
                ])->exists();

                if (!$exists) {
                    $creados[] = Disponibilidad::create([
                        'especialista_id' => $especialistaId,
                        'fecha' => $bloque['fecha'],
                        'hora_inicio' => $bloque['hora_inicio'],
                        'hora_fin' => $bloque['hora_fin'],
                        'esta_reservada' => false
                    ]);
                }
            }
            return $creados;
        });
    }

    /**
     * Eliminar bloques de disponibilidad que no estén reservados.
     */
    public function removeAvailabilitySlot(int $especialistaId, int $slotId)
    {
        return Disponibilidad::where('id', $slotId)
            ->where('especialista_id', $especialistaId)
            ->where('esta_reservada', false)
            ->delete();
    }

    /**
     * Obtener todos los pacientes asignados a un especialista.
     */
    public function getAssignedPatients(int $especialistaId)
    {
        /**
         * Obtiene los pacientes asignados al especialista.
         * Se utiliza una subconsulta para obtener la fecha de la última cita sin incurrir en N+1.
         */
        return Usuario::whereHas('citas', function ($query) use ($especialistaId) {
            $query->where('especialista_id', $especialistaId);
        })
        ->with(['paciente']) // Perfil extendido del paciente (teléfono, etc)
        ->withCount(['citas' => function ($query) use ($especialistaId) {
            $query->where('especialista_id', $especialistaId);
        }])
        ->addSelect([
            'ultima_cita_fecha' => Cita::select('disponibilidades.fecha')
                ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                ->whereColumn('citas.paciente_id', 'usuarios.id')
                ->where('citas.especialista_id', $especialistaId)
                ->orderBy('disponibilidades.fecha', 'desc')
                ->limit(1),
            // Próxima cita activa futura (para seguimiento / carga mental)
            'proxima_cita_fecha' => Cita::select('disponibilidades.fecha')
                ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                ->whereColumn('citas.paciente_id', 'usuarios.id')
                ->where('citas.especialista_id', $especialistaId)
                ->whereIn('citas.estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])
                ->whereDate('disponibilidades.fecha', '>=', now()->toDateString())
                ->orderBy('disponibilidades.fecha', 'asc')
                ->limit(1),
            // Última interacción real: última cita ya pasada
            'ultima_interaccion' => Cita::select('disponibilidades.fecha')
                ->join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
                ->whereColumn('citas.paciente_id', 'usuarios.id')
                ->where('citas.especialista_id', $especialistaId)
                ->whereDate('disponibilidades.fecha', '<', now()->toDateString())
                ->orderBy('disponibilidades.fecha', 'desc')
                ->limit(1),
            // Alertas de crisis activas de este paciente
            'crisis_count' => NotaEspecialista::selectRaw('count(*)')
                ->whereColumn('notas_especialistas.paciente_id', 'usuarios.id')
                ->where('notas_especialistas.especialista_id', $especialistaId)
                ->where('notas_especialistas.tipo', NotaEspecialista::TIPO_CRISIS),
        ])
        ->get();
    }

    /**
     * Sesiones de hoy del especialista (agenda del día), ordenadas por hora.
     */
    public function getTodaySessions(int $especialistaId)
    {
        return Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->whereDate('disponibilidades.fecha', now()->toDateString())
            ->whereNotIn('citas.estado', [Cita::STATUS_CANCELLED, Cita::STATUS_RESCHEDULED])
            ->with(['paciente', 'disponibilidad'])
            ->select('citas.*')
            // Pendientes de atender primero; las ya realizadas al final; luego por hora.
            ->orderByRaw("CASE WHEN citas.estado = ? THEN 1 ELSE 0 END", [Cita::STATUS_COMPLETED])
            ->orderBy('disponibilidades.hora_inicio', 'asc')
            ->get();
    }

    public function countSessionsByDate(int $especialistaId, string $date, array $states = [Cita::STATUS_RESERVADA, Cita::STATUS_ATTENTION, Cita::STATUS_COMPLETED]): int
    {
        return (int) Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->whereDate('disponibilidades.fecha', $date)
            ->whereIn('citas.estado', $states)
            ->selectRaw('COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->value('total');
    }

    /**
     * Tareas pendientes (no completadas) que el especialista dejó a sus pacientes,
     * con la próxima cita de cada paciente para saber cuándo preguntar por ellas.
     */
    public function getPendingTasks(int $especialistaId)
    {
        $tareas = \App\Models\TareaPaciente::where('especialista_id', $especialistaId)
            ->where('completada', false)
            ->with('paciente')
            ->latest()
            ->get();

        // Mapa paciente_id => próxima cita (fecha) para indicar en qué sesión preguntar
        $proximas = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->whereIn('citas.estado', [Cita::STATUS_RESERVADA, Cita::STATUS_RESCHEDULED, Cita::STATUS_ATTENTION])
            ->whereDate('disponibilidades.fecha', '>=', now()->toDateString())
            ->selectRaw('citas.paciente_id, MIN(disponibilidades.fecha) as fecha')
            ->groupBy('citas.paciente_id')
            ->pluck('fecha', 'citas.paciente_id');

        return $tareas->map(function ($t) use ($proximas) {
            $t->proxima_cita = $proximas[$t->paciente_id] ?? null;
            return $t;
        });
    }

    /**
     * Métricas reales del especialista para la pestaña "Métricas".
     * Sin datos monetarios ni valoración (removidos por requerimiento).
     */
    public function getRealMetrics(int $especialistaId): array
    {
        $now = now();

        $citasHoy = (int) Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->whereDate('disponibilidades.fecha', $now->toDateString())
            ->where('citas.estado', '!=', Cita::STATUS_CANCELLED)
            ->selectRaw('COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->value('total');

        $citasMes = (int) Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->where('citas.estado', '!=', Cita::STATUS_CANCELLED)
            ->selectRaw('COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->value('total');

        $completadasMes = (int) Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->where('citas.estado', Cita::STATUS_COMPLETED)
            ->selectRaw('COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as total')
            ->value('total');

        $pacientesUnicos = Cita::where('especialista_id', $especialistaId)->distinct('paciente_id')->count('paciente_id');

        // Productividad: citas por día de la semana en el mes actual (Lun..Sáb)
        $porDia = Cita::join('disponibilidades', 'citas.disponibilidad_id', '=', 'disponibilidades.id')
            ->where('citas.especialista_id', $especialistaId)
            ->whereMonth('disponibilidades.fecha', $now->month)
            ->whereYear('disponibilidades.fecha', $now->year)
            ->where('citas.estado', '!=', Cita::STATUS_CANCELLED)
            ->selectRaw('disponibilidades.fecha, COALESCE(SUM(COALESCE(citas.cantidad_sesiones, 1)), 0) as sesiones')
            ->groupBy('disponibilidades.fecha')
            ->get()
            ->groupBy(fn ($c) => Carbon::parse($c->fecha)->dayOfWeekIso)
            ->map(fn ($group) => $group->sum('sesiones'));

        $diasSemana = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'];
        $prodValues = [];
        foreach ($diasSemana as $iso => $nombre) {
            $prodValues[] = (int) ($porDia[$iso] ?? 0);
        }

        // Tipos de paciente (real): nuevos (1ª cita este mes), recurrentes (>=2 citas), puntuales (1 cita histórica)
        $conteos = Cita::where('especialista_id', $especialistaId)
            ->selectRaw('paciente_id, COUNT(*) as total, MIN(created_at) as primera')
            ->groupBy('paciente_id')
            ->get();

        $nuevos = $conteos->filter(fn ($c) => Carbon::parse($c->primera)->isSameMonth($now))->count();
        $recurrentes = $conteos->filter(fn ($c) => $c->total >= 2)->count();
        $puntuales = $conteos->filter(fn ($c) => $c->total == 1 && !Carbon::parse($c->primera)->isSameMonth($now))->count();

        return [
            'kpis' => [
                'citas_hoy'        => $citasHoy,
                'citas_mes'        => $citasMes,
                'completadas_mes'  => $completadasMes,
                'pacientes_tot'    => $pacientesUnicos,
            ],
            'charts' => [
                'prod_labels' => array_values($diasSemana),
                'prod_values' => $prodValues,
                'type_labels' => ['Nuevos', 'Recurrentes', 'Puntuales'],
                'type_values' => [$nuevos, $recurrentes, $puntuales],
            ],
        ];
    }

    /**
     * Obtener historia clínica completa para un paciente en contexto de un especialista.
     */
    public function getPatientHistory(int $especialistaId, int $pacienteId)
    {
        return Cita::where('especialista_id', $especialistaId)
            ->where('paciente_id', $pacienteId)
            ->with(['disponibilidad', 'tareas'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtener todas las notas privadas de un especialista.
     */
    public function getEspecialistaNotes(int $especialistaId)
    {
        return NotaEspecialista::where('especialista_id', $especialistaId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Crear una nueva nota privada.
     */
    public function createNote(int $especialistaId, array $data)
    {
        return NotaEspecialista::create([
            'especialista_id' => $especialistaId,
            'titulo' => $data['titulo'],
            'contenido' => $data['contenido'],
        ]);
    }

    /**
     * Actualizar una nota existente.
     */
    public function updateNote(int $especialistaId, int $noteId, array $data)
    {
        $note = NotaEspecialista::where('id', $noteId)
            ->where('especialista_id', $especialistaId)
            ->firstOrFail();

        $note->update($data);
        return $note;
    }

    /**
     * Eliminar una nota.
     */
    public function deleteNote(int $especialistaId, int $noteId)
    {
        return NotaEspecialista::where('id', $noteId)
            ->where('especialista_id', $especialistaId)
            ->delete();
    }

    /**
     * Crear un nuevo especialista (Usuario con rol doctor + perfil).
     */
    public function createEspecialista(array $data): Especialista
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear el usuario
            $usuario = Usuario::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
                'rol_id' => 2, // Asumiendo que 2 es 'doctor' basado en observaciones anteriores
            ]);

            // 2. Crear el perfil de especialista
            $especialista = Especialista::create([
                'usuario_id' => $usuario->id,
                'especialidad' => $data['especialidad'] ?? 'Psicología General',
                'biografia' => $data['biografia'] ?? 'Perfil profesional en construcción.',
                'foto' => $data['foto'] ?? null,
            ]);

            // 3. Asociar servicios iniciales si vienen
            if (isset($data['servicios'])) {
                $especialista->servicios()->sync($data['servicios']);
            }

            return $especialista;
        });
    }

    /**
     * Actualizar datos de un especialista existente.
     */
    public function updateEspecialista(int $id, array $data): Especialista
    {
        return DB::transaction(function () use ($id, $data) {
            $especialista = Especialista::findOrFail($id);
            $usuario = $especialista->usuario;

            // 1. Actualizar datos de usuario
            $userData = [
                'nombre' => $data['nombre'],
                'email' => $data['email'],
            ];

            if (!empty($data['password'])) {
                $userData['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
            }

            $usuario->update($userData);

            // 2. Actualizar perfil de especialista
            $especialista->update([
                'especialidad' => $data['especialidad'],
                'biografia' => $data['biografia'] ?? $especialista->biografia,
                'foto' => $data['foto'] ?? $especialista->foto,
            ]);

            // 3. Sincronizar servicios si se proporcionan
            if (isset($data['servicios'])) {
                $especialista->servicios()->sync($data['servicios']);
            }

            return $especialista;
        });
    }

    /**
     * Sincronizar los servicios que ofrece un especialista.
     */
    public function updateEspecialistaServices(int $especialistaId, array $serviceIds)
    {
        $especialista = Especialista::findOrFail($especialistaId);
        return $especialista->servicios()->sync($serviceIds);
    }
}
