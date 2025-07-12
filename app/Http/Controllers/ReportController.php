<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HousingUnit;
use App\Models\Occupier;
use App\Models\AuditLog;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function occupancy()
    {
        $totalUnits = HousingUnit::where('is_active', true)->count();
        $occupiedUnits = HousingUnit::where('is_active', true)
            ->where('is_occupied', true)
            ->count();
        $vacantUnits = $totalUnits - $occupiedUnits;
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        // Units by type
        $unitsByType = HousingUnit::with('housingType')
            ->where('is_active', true)
            ->get()
            ->groupBy('housingType.name')
            ->map(function ($units) {
                return [
                    'total' => $units->count(),
                    'occupied' => $units->where('is_occupied', true)->count(),
                    'vacant' => $units->where('is_occupied', false)->count(),
                ];
            });

        // Occupied units with details
        $occupiedUnitDetails = HousingUnit::with(['housingType', 'currentOccupier'])
            ->where('is_active', true)
            ->where('is_occupied', true)
            ->get();

        return view('reports.occupancy', compact(
            'totalUnits',
            'occupiedUnits',
            'vacantUnits',
            'occupancyRate',
            'unitsByType',
            'occupiedUnitDetails'
        ));
    }

    public function vacancy()
    {
        $vacantUnits = HousingUnit::with(['housingType'])
            ->where('is_active', true)
            ->where('is_occupied', false)
            ->get();

        $totalVacant = $vacantUnits->count();
        $totalUnits = HousingUnit::where('is_active', true)->count();
        $vacancyRate = $totalUnits > 0 ? round(($totalVacant / $totalUnits) * 100, 1) : 0;

        // Vacant units by type
        $vacantByType = $vacantUnits->groupBy('housingType.name')
            ->map(function ($units) {
                return $units->count();
            });

        // Vacant units by condition
        $vacantByCondition = $vacantUnits->groupBy('condition_grade')
            ->map(function ($units) {
                return $units->count();
            });

        // Potential revenue from vacant units
        $potentialRevenue = $vacantUnits->sum('rental_rate');

        return view('reports.vacancy', compact(
            'vacantUnits',
            'totalVacant',
            'totalUnits',
            'vacancyRate',
            'vacantByType',
            'vacantByCondition',
            'potentialRevenue'
        ));
    }

    public function activity()
    {
        $recentActivities = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Activity by type
        $activityByType = AuditLog::selectRaw('action, count(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('action')
            ->pluck('count', 'action');

        // Activity by day (last 30 days)
        $activityByDay = AuditLog::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Top active users
        $topUsers = AuditLog::with('user')
            ->selectRaw('user_id, count(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('reports.activity', compact(
            'recentActivities',
            'activityByType',
            'activityByDay',
            'topUsers'
        ));
    }
}