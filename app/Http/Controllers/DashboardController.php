<?php

namespace App\Http\Controllers;

use App\Models\LandingImage;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * @param DashboardService $dashboardService
     */
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        // Si no está autenticado, mostrar la landing page pública
        if (!$user) {
            $stats = $this->dashboardService->getDashboardStats();
            $landingImages = LandingImage::orderBy('sort_order')->get();
            return view('dashboard', compact('stats', 'landingImages'));
        }

        // Redirigir según el rol del usuario autenticado
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isDoctor()) {
            return redirect()->route('doctor.dashboard');
        } elseif ($user->isReceptionist()) {
            return redirect()->route('receptionist.dashboard');
        } elseif ($user->isPatient()) {
            return redirect()->route('portal.index');
        }

        // Fallback genérico para otros roles o usuarios sin rol específico
        $stats = $this->dashboardService->getDashboardStats();
        $landingImages = LandingImage::orderBy('sort_order')->get();
        return view('dashboard', compact('stats', 'landingImages'));
    }
}
