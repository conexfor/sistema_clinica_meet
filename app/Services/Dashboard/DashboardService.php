<?php

namespace App\Services\Dashboard;

class DashboardService
{
    /**
     * Get the dashboard statistics
     */
    public function getDashboardStats(): array
    {
        // En el futuro, aquí se consultarán los modelos
        return [
            'active_patients' => 142,
            'today_sessions' => 8,
            'upcoming_appointments' => 24,
            'monthly_revenue' => 4500,
        ];
    }
}
