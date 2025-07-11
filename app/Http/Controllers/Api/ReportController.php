<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HousingUnit;
use App\Models\HousingType;
use App\Models\Occupier;
use App\Models\Note;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function dashboard()
    {
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
        
        // Lease expiring soon (within 30 days)
        $expiringLeases = Occupier::where('is_active', true)
            ->where('lease_end_date', '>=', Carbon::now())
            ->where('lease_end_date', '<=', Carbon::now()->addDays(30))
            ->count();
        
        // High priority notes
        $highPriorityNotes = Note::whereIn('priority', ['high', 'urgent'])->count();
        
        // Units needing attention (poor condition)
        $poorConditionUnits = HousingUnit::active()
            ->whereIn('condition_grade', ['D', 'F'])
            ->count();
        
        AuditLog::logActivity('view', new HousingUnit(), null, null, 'Viewed dashboard via API');
        
        return response()->json([
            'statistics' => [
                'housing' => [
                    'total_units' => $totalUnits,
                    'occupied_units' => $occupiedUnits,
                    'vacant_units' => $vacantUnits,
                    'occupancy_rate' => $occupancyRate,
                    'poor_condition_units' => $poorConditionUnits,
                ],
                'financial' => [
                    'monthly_revenue' => $monthlyRevenue,
                    'yearly_revenue' => $yearlyRevenue,
                    'potential_revenue' => $potentialRevenue,
                    'revenue_efficiency' => $revenueEfficiency,
                ],
                'occupiers' => [
                    'total_occupiers' => Occupier::current()->count(),
                    'expiring_leases' => $expiringLeases,
                ],
                'notes' => [
                    'total_notes' => Note::count(),
                    'high_priority_notes' => $highPriorityNotes,
                ],
            ],
            'alerts' => [
                'expiring_leases' => $expiringLeases,
                'high_priority_notes' => $highPriorityNotes,
                'poor_condition_units' => $poorConditionUnits,
            ],
        ], 200);
    }
    
    public function occupancy(Request $request)
    {
        $query = HousingUnit::with(['housingType', 'occupier'])->active();
        
        // Housing type filter
        if ($request->filled('housing_type')) {
            $query->where('housing_type_id', $request->housing_type);
        }
        
        // Occupancy filter
        if ($request->filled('occupancy_status')) {
            $query->where('is_occupied', $request->occupancy_status === 'occupied');
        }
        
        $units = $query->get();
        
        // Calculate statistics
        $stats = [
            'total_units' => $units->count(),
            'occupied_units' => $units->where('is_occupied', true)->count(),
            'vacant_units' => $units->where('is_occupied', false)->count(),
            'occupancy_rate' => $units->count() > 0 ? 
                round(($units->where('is_occupied', true)->count() / $units->count()) * 100, 1) : 0,
            'monthly_revenue' => $units->where('is_occupied', true)->sum('rental_rate'),
            'potential_revenue' => $units->sum('rental_rate'),
        ];
        
        // Housing type breakdown
        $housingTypeStats = HousingType::with('housingUnits')
            ->get()
            ->map(function ($type) {
                $totalUnits = $type->housingUnits->where('is_active', true)->count();
                $occupiedUnits = $type->housingUnits->where('is_active', true)->where('is_occupied', true)->count();
                
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'total_units' => $totalUnits,
                    'occupied_units' => $occupiedUnits,
                    'vacant_units' => $totalUnits - $occupiedUnits,
                    'occupancy_rate' => $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0,
                    'monthly_revenue' => $type->housingUnits->where('is_active', true)->where('is_occupied', true)->sum('rental_rate'),
                ];
            });
        
        AuditLog::logActivity('view', new HousingUnit(), null, null, 'Viewed occupancy report via API');
        
        return response()->json([
            'statistics' => $stats,
            'housing_type_breakdown' => $housingTypeStats,
            'units' => $units,
        ], 200);
    }
    
    public function vacancy(Request $request)
    {
        $query = HousingUnit::with(['housingType'])->active()->vacant();
        
        // Housing type filter
        if ($request->filled('housing_type')) {
            $query->where('housing_type_id', $request->housing_type);
        }
        
        // Condition filter
        if ($request->filled('condition')) {
            $query->where('condition_grade', $request->condition);
        }
        
        $vacantUnits = $query->get();
        
        // Calculate statistics
        $stats = [
            'total_vacant' => $vacantUnits->count(),
            'lost_revenue' => $vacantUnits->sum('rental_rate'),
            'average_rent' => $vacantUnits->avg('rental_rate'),
            'total_units' => HousingUnit::active()->count(),
            'vacancy_rate' => HousingUnit::active()->count() > 0 ? 
                round(($vacantUnits->count() / HousingUnit::active()->count()) * 100, 1) : 0,
        ];
        
        // Vacant units by housing type
        $vacantByType = $vacantUnits->groupBy('housing_type_id')
            ->map(function ($units, $typeId) {
                $type = HousingType::find($typeId);
                return [
                    'type_name' => $type->name,
                    'count' => $units->count(),
                    'lost_revenue' => $units->sum('rental_rate'),
                    'average_rent' => $units->avg('rental_rate'),
                ];
            });
        
        AuditLog::logActivity('view', new HousingUnit(), null, null, 'Viewed vacancy report via API');
        
        return response()->json([
            'statistics' => $stats,
            'vacant_by_type' => $vacantByType,
            'vacant_units' => $vacantUnits,
        ], 200);
    }
    
    public function activity(Request $request)
    {
        $query = AuditLog::with('user');
        
        // Date range filter
        $dateFrom = $request->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        
        $query->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        
        // User filter
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }
        
        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Model filter
        if ($request->filled('model')) {
            $query->where('model_type', 'like', '%' . $request->model . '%');
        }
        
        // Pagination
        $perPage = $request->get('per_page', 50);
        $activities = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Statistics
        $stats = [
            'total_activities' => $activities->total(),
            'unique_users' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->distinct('user_id')->count(),
        ];
        
        // Activity by action
        $activityByAction = AuditLog::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();
        
        AuditLog::logActivity('view', new AuditLog(), null, null, 'Viewed activity report via API');
        
        return response()->json([
            'activities' => $activities->items(),
            'pagination' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
            'statistics' => $stats,
            'activity_by_action' => $activityByAction,
        ], 200);
    }
}