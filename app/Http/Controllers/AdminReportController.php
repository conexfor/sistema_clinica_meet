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
    /**
     * Finanzas: ingresos MoM, pacientes activos, retención, sesiones y modalidad.
     */
    public function finanzas(Request $request, FinanceReportService $service): \Illuminate\View\View
    {
        $period = $request->get('period', 'month');
        $stats = $service->getStats($period);

        return view('admin.reportes.finanzas', compact('stats', 'period'));
    }

    /**
     * Desempeño / RRHH: ranking de especialistas, conversión e inactivos.
     */
    public function desempeno(Request $request, PerformanceReportService $service): \Illuminate\View\View
    {
        $period = $request->get('period', 'month');
        $stats = $service->getStats($period);

        return view('admin.reportes.desempeno', compact('stats', 'period'));
    }

    /**
     * Ocupación / Logística: mapa de calor y comparativo presencial vs virtual.
     */
    public function ocupacion(Request $request, OccupancyReportService $service): \Illuminate\View\View
    {
        $period = $request->get('period', 'month');
        $stats = $service->getStats($period);

        return view('admin.reportes.ocupacion', compact('stats', 'period'));
    }

    /**
     * Panel de control: ausentismo, picos de cancelación y placeholders de Fase 2.
     */
    public function control(Request $request, ControlPanelService $service): \Illuminate\View\View
    {
        $period = $request->get('period', 'month');
        $stats = $service->getStats($period);

        return view('admin.reportes.control', compact('stats', 'period'));
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

        $period = $request->get('period', 'month');

        return response()->json($service->resolve($metric, $period));
    }
}
