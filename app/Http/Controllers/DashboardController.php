<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HousingUnit;
use App\Models\Occupier;
use App\Models\User;
use App\Models\Note;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    public function index()
    {
        // Get basic statistics
        $totalUnits = HousingUnit::where('is_active', true)->count();
        $occupiedUnits = HousingUnit::where('is_active', true)
            ->where('is_occupied', true)
            ->count();
        $vacantUnits = $totalUnits - $occupiedUnits;
        $totalOccupiers = Occupier::where('is_current', true)->count();
        $totalUsers = User::where('is_active', true)->count();
        $totalNotes = Note::where('is_active', true)->count();

        // Recent activities
        $recentActivities = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent notes
        $recentNotes = Note::with(['user', 'housingUnit', 'occupier'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Units by condition
        $unitsByCondition = HousingUnit::where('is_active', true)
            ->selectRaw('condition_grade, count(*) as count')
            ->groupBy('condition_grade')
            ->pluck('count', 'condition_grade');

        // Occupancy rate
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        return view('dashboard.index', compact(
            'totalUnits',
            'occupiedUnits',
            'vacantUnits',
            'totalOccupiers',
            'totalUsers',
            'totalNotes',
            'recentActivities',
            'recentNotes',
            'unitsByCondition',
            'occupancyRate'
        ));
    }
}