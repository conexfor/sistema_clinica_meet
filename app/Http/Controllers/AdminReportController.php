<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Services\Admin\ControlPanelService;
use App\Services\Admin\DrillDownService;
use App\Services\Admin\FinanceReportService;
use App\Services\Admin\OccupancyReportService;
use App\Services\Admin\PerformanceReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sub-páginas de reportes y analíticas del admin, más el endpoint de drill-down
 * ("modo Lupa"). Todas las páginas aceptan ?period= para filtrar por tiempo.
 */
class AdminReportController extends Controller
{
    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('fecha_inicio', $request->get('start_date'));
        $endDate = $request->get('fecha_fin', $request->get('end_date'));

        if ($period === 'custom' && $startDate && $endDate) {
            return ['period' => 'custom', 'start_date' => $startDate, 'end_date' => $endDate];
        }

        return ['period' => $period, 'start_date' => null, 'end_date' => null];
    }

    /**
     * Finanzas: ingresos MoM, pacientes activos, retención, sesiones y modalidad.
     */
    public function finanzas(Request $request, FinanceReportService $service): \Illuminate\View\View
    {
        $filter = $this->resolvePeriod($request);
        $stats = $service->getStats($filter['period'], $filter['start_date'], $filter['end_date']);

        return view('admin.reportes.finanzas', ['stats' => $stats, 'period' => $filter['period'], 'fecha_inicio' => $filter['start_date'], 'fecha_fin' => $filter['end_date']]);
    }

    /**
     * Desempeño / RRHH: ranking de especialistas, conversión e inactivos.
     */
    public function desempeno(Request $request, PerformanceReportService $service): \Illuminate\View\View
    {
        $filter = $this->resolvePeriod($request);
        $stats = $service->getStats($filter['period'], $filter['start_date'], $filter['end_date']);

        return view('admin.reportes.desempeno', ['stats' => $stats, 'period' => $filter['period'], 'fecha_inicio' => $filter['start_date'], 'fecha_fin' => $filter['end_date']]);
    }

    /**
     * Ocupación / Logística: mapa de calor y comparativo presencial vs virtual.
     */
    public function ocupacion(Request $request, OccupancyReportService $service): \Illuminate\View\View
    {
        $filter = $this->resolvePeriod($request);
        $stats = $service->getStats($filter['period'], $filter['start_date'], $filter['end_date']);

        return view('admin.reportes.ocupacion', ['stats' => $stats, 'period' => $filter['period'], 'fecha_inicio' => $filter['start_date'], 'fecha_fin' => $filter['end_date']]);
    }

    /**
     * Panel de control: ausentismo, picos de cancelación y placeholders de Fase 2.
     */
    public function control(Request $request, ControlPanelService $service): \Illuminate\View\View
    {
        $filter = $this->resolvePeriod($request);
        $stats = $service->getStats($filter['period'], $filter['start_date'], $filter['end_date']);

        return view('admin.reportes.control', ['stats' => $stats, 'period' => $filter['period'], 'fecha_inicio' => $filter['start_date'], 'fecha_fin' => $filter['end_date']]);
    }

    /**
     * Log de auditoría: registro paginado de acciones sensibles, con filtro por categoría.
     */
    public function auditoria(Request $request): \Illuminate\View\View
    {
        $categoria = $request->get('categoria');

        $eventos = Auditoria::with('usuario.rol')
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $categorias = [
            Auditoria::CAT_AUTH      => 'Autenticación',
            Auditoria::CAT_FINANZAS  => 'Finanzas',
            Auditoria::CAT_PACIENTES => 'Pacientes',
            Auditoria::CAT_SISTEMA   => 'Sistema',
        ];

        return view('admin.reportes.auditoria', compact('eventos', 'categorias', 'categoria'));
    }

    /**
     * Drill-down ("modo Lupa"): devuelve la lista de registros detrás de un KPI.
     */
    public function drill(Request $request, string $metric, DrillDownService $service): JsonResponse
    {
        abort_unless(in_array($metric, DrillDownService::METRICS, true), 404);

        $filter = $this->resolvePeriod($request);

        return response()->json($service->resolve($metric, $filter['period'], $filter['start_date'], $filter['end_date']));
    }
}
