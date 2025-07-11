<?php

namespace App\Http\Controllers;

use App\Models\HousingUnit;
use App\Models\Occupier;
use App\Models\Note;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\HousingType;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Basic statistics
        $totalUnits = HousingUnit::active()->count();
        $occupiedUnits = HousingUnit::active()->occupied()->count();
        $vacantUnits = $totalUnits - $occupiedUnits;
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;
        
        // Financial statistics
        $monthlyRevenue = HousingUnit::active()->occupied()->sum('rental_rate');
        $yearlyRevenue = $monthlyRevenue * 12;
        $potentialRevenue = HousingUnit::active()->sum('rental_rate');
        $revenueEfficiency = $potentialRevenue > 0 ? round(($monthlyRevenue / $potentialRevenue) * 100, 1) : 0;
        
        // Recent activity
        $recentNotes = Note::with(['user', 'housingUnit', 'occupier'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Lease expiring soon (within 30 days)
        $expiringLeases = [];
        if ($user->canManageHousing()) {
            $expiringLeases = Occupier::with(['housingUnit'])
                ->where('is_active', true)
                ->where('lease_end_date', '>=', Carbon::now())
                ->where('lease_end_date', '<=', Carbon::now()->addDays(30))
                ->orderBy('lease_end_date', 'asc')
                ->get();
        }
        
        // Housing type distribution
        $housingTypeStats = HousingType::with('housingUnits')
            ->get()
            ->map(function ($type) {
                return [
                    'name' => $type->name,
                    'total_units' => $type->housingUnits->count(),
                    'occupied_units' => $type->housingUnits->where('is_occupied', true)->count(),
                    'occupancy_rate' => $type->getOccupancyRateAttribute(),
                    'monthly_revenue' => $type->getTotalMonthlyIncomeAttribute(),
                ];
            });
        
        // Recent system activity for admins
        $recentAudits = [];
        if ($user->isAdmin()) {
            $recentAudits = AuditLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        
        // Condition grade summary
        $conditionGrades = HousingUnit::active()
            ->selectRaw('condition_grade, COUNT(*) as count')
            ->groupBy('condition_grade')
            ->get()
            ->pluck('count', 'condition_grade');
        
        // Monthly occupancy trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyTrend[] = [
                'month' => $month->format('M Y'),
                'occupancy_rate' => $occupancyRate, // In real system, this would be historical data
                'revenue' => $monthlyRevenue,
            ];
        }
        
        // Priorities and alerts
        $alerts = [];
        
        // High priority notes
        $highPriorityNotes = Note::where('priority', 'high')
            ->orWhere('priority', 'urgent')
            ->count();
        
        if ($highPriorityNotes > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "You have {$highPriorityNotes} high priority notes requiring attention.",
                'action' => route('notes.index', ['priority' => 'high'])
            ];
        }
        
        // Expiring leases
        if ($expiringLeases->count() > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "You have {$expiringLeases->count()} leases expiring within 30 days.",
                'action' => route('occupiers.index', ['expiring' => 'true'])
            ];
        }
        
        // Units needing attention (poor condition)
        $poorConditionUnits = HousingUnit::active()
            ->whereIn('condition_grade', ['D', 'F'])
            ->count();
        
        if ($poorConditionUnits > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "You have {$poorConditionUnits} units in poor condition requiring maintenance.",
                'action' => route('housing-units.index', ['condition' => 'poor'])
            ];
        }
        
        return view('dashboard.index', compact(
            'totalUnits',
            'occupiedUnits',
            'vacantUnits',
            'occupancyRate',
            'monthlyRevenue',
            'yearlyRevenue',
            'potentialRevenue',
            'revenueEfficiency',
            'recentNotes',
            'expiringLeases',
            'housingTypeStats',
            'recentAudits',
            'conditionGrades',
            'monthlyTrend',
            'alerts',
            'user'
        ));
    }
}