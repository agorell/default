<?php

namespace App\Http\Controllers;

use App\Models\HousingUnit;
use App\Models\HousingType;
use App\Models\Occupier;
use App\Models\Note;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Basic statistics
        $stats = [
            'total_units' => HousingUnit::active()->count(),
            'occupied_units' => HousingUnit::active()->occupied()->count(),
            'vacant_units' => HousingUnit::active()->vacant()->count(),
            'total_occupiers' => Occupier::current()->count(),
            'monthly_revenue' => HousingUnit::active()->occupied()->sum('rental_rate'),
            'total_notes' => Note::count(),
            'high_priority_notes' => Note::whereIn('priority', ['high', 'urgent'])->count(),
        ];
        
        $stats['occupancy_rate'] = $stats['total_units'] > 0 ? 
            round(($stats['occupied_units'] / $stats['total_units']) * 100, 1) : 0;
        
        AuditLog::logActivity('view', new HousingUnit(), null, null, 'Viewed reports dashboard');
        
        return view('reports.index', compact('stats', 'user'));
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
        
        // Condition grade breakdown
        $conditionStats = HousingUnit::active()
            ->selectRaw('
                condition_grade,
                COUNT(*) as total_units,
                SUM(CASE WHEN is_occupied = 1 THEN 1 ELSE 0 END) as occupied_units,
                SUM(CASE WHEN is_occupied = 0 THEN 1 ELSE 0 END) as vacant_units,
                SUM(rental_rate) as potential_revenue,
                SUM(CASE WHEN is_occupied = 1 THEN rental_rate ELSE 0 END) as actual_revenue
            ')
            ->groupBy('condition_grade')
            ->get()
            ->map(function ($item) {
                return [
                    'grade' => $item->condition_grade,
                    'grade_name' => $this->getConditionGradeName($item->condition_grade),
                    'total_units' => $item->total_units,
                    'occupied_units' => $item->occupied_units,
                    'vacant_units' => $item->vacant_units,
                    'occupancy_rate' => $item->total_units > 0 ? 
                        round(($item->occupied_units / $item->total_units) * 100, 1) : 0,
                    'potential_revenue' => $item->potential_revenue,
                    'actual_revenue' => $item->actual_revenue,
                ];
            });
        
        $housingTypes = HousingType::active()->get();
        
        AuditLog::logActivity('view', new HousingUnit(), null, null, 'Viewed occupancy report');
        
        return view('reports.occupancy', compact(
            'units', 
            'stats', 
            'housingTypeStats', 
            'conditionStats', 
            'housingTypes'
        ));
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
        
        // Rent range filter
        if ($request->filled('min_rent')) {
            $query->where('rental_rate', '>=', $request->min_rent);
        }
        if ($request->filled('max_rent')) {
            $query->where('rental_rate', '<=', $request->max_rent);
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
        
        // Vacant units by condition
        $vacantByCondition = $vacantUnits->groupBy('condition_grade')
            ->map(function ($units, $grade) {
                return [
                    'grade' => $grade,
                    'grade_name' => $this->getConditionGradeName($grade),
                    'count' => $units->count(),
                    'lost_revenue' => $units->sum('rental_rate'),
                    'average_rent' => $units->avg('rental_rate'),
                ];
            });
        
        $housingTypes = HousingType::active()->get();
        $conditionGrades = ['A', 'B', 'C', 'D', 'F'];
        
        AuditLog::logActivity('view', new HousingUnit(), null, null, 'Viewed vacancy report');
        
        return view('reports.vacancy', compact(
            'vacantUnits', 
            'stats', 
            'vacantByType', 
            'vacantByCondition', 
            'housingTypes', 
            'conditionGrades'
        ));
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
        
        $activities = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Statistics
        $stats = [
            'total_activities' => $activities->total(),
            'unique_users' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->distinct('user_id')->count(),
            'most_active_user' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
                ->selectRaw('user_id, COUNT(*) as activity_count')
                ->groupBy('user_id')
                ->orderBy('activity_count', 'desc')
                ->with('user')
                ->first(),
        ];
        
        // Activity by action
        $activityByAction = AuditLog::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();
        
        // Activity by model
        $activityByModel = AuditLog::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('model_type, COUNT(*) as count')
            ->groupBy('model_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                $modelParts = explode('\\', $item->model_type);
                $item->model_name = end($modelParts);
                return $item;
            });
        
        // Daily activity trend
        $dailyActivity = AuditLog::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $users = User::active()->get();
        $actions = AuditLog::distinct()->pluck('action');
        
        AuditLog::logActivity('view', new AuditLog(), null, null, 'Viewed activity report');
        
        return view('reports.activity', compact(
            'activities', 
            'stats', 
            'activityByAction', 
            'activityByModel', 
            'dailyActivity',
            'users', 
            'actions',
            'dateFrom',
            'dateTo'
        ));
    }
    
    private function getConditionGradeName($grade)
    {
        $grades = [
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Fair',
            'D' => 'Poor',
            'F' => 'Critical'
        ];
        
        return $grades[$grade] ?? 'Unknown';
    }
}