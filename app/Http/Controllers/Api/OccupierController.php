<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Occupier;
use App\Models\HousingUnit;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OccupierController extends Controller
{
    public function index(Request $request)
    {
        $query = Occupier::with(['housingUnit.housingType']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Housing unit filter
        if ($request->filled('unit')) {
            $query->where('housing_unit_id', $request->unit);
        }
        
        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->whereNull('move_out_date');
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false)->orWhereNotNull('move_out_date');
            }
        }
        
        // Lease expiring filter
        if ($request->filled('expiring') && $request->expiring === 'true') {
            $query->where('is_active', true)
                  ->where('lease_end_date', '>=', Carbon::now())
                  ->where('lease_end_date', '<=', Carbon::now()->addDays(30));
        }
        
        // Pagination
        $perPage = $request->get('per_page', 25);
        $occupiers = $query->paginate($perPage);
        
        AuditLog::logActivity('view', new Occupier(), null, null, 'Viewed occupiers via API');
        
        return response()->json([
            'occupiers' => $occupiers->items(),
            'pagination' => [
                'current_page' => $occupiers->currentPage(),
                'last_page' => $occupiers->lastPage(),
                'per_page' => $occupiers->perPage(),
                'total' => $occupiers->total(),
            ],
            'statistics' => [
                'total' => Occupier::count(),
                'active' => Occupier::current()->count(),
                'inactive' => Occupier::where('is_active', false)->count(),
                'expiring_soon' => Occupier::where('is_active', true)
                    ->where('lease_end_date', '>=', Carbon::now())
                    ->where('lease_end_date', '<=', Carbon::now()->addDays(30))
                    ->count(),
                'total_monthly_rent' => Occupier::current()->sum('rental_amount'),
            ],
        ], 200);
    }
    
    public function show(Occupier $occupier)
    {
        $occupier->load(['housingUnit.housingType', 'notes.user']);
        
        AuditLog::logActivity('view', $occupier, null, null, "Viewed occupier via API: {$occupier->name}");
        
        return response()->json([
            'occupier' => $occupier,
            'statistics' => [
                'total_notes' => $occupier->notes()->count(),
                'recent_notes' => $occupier->notes()->where('created_at', '>=', now()->subDays(30))->count(),
                'occupancy_duration' => $occupier->getOccupancyDurationAttribute(),
                'lease_status' => $occupier->getLeaseStatusAttribute(),
                'days_until_lease_expiry' => $occupier->getDaysUntilLeaseExpiryAttribute(),
                'total_rent_paid' => $occupier->getTotalRentPaidAttribute(),
            ],
        ], 200);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'housing_unit_id' => ['required', 'exists:housing_units,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'move_in_date' => ['required', 'date'],
            'lease_start_date' => ['required', 'date'],
            'lease_end_date' => ['required', 'date', 'after:lease_start_date'],
            'rental_amount' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'is_active' => ['boolean'],
        ]);
        
        // Check if housing unit is available
        $housingUnit = HousingUnit::find($request->housing_unit_id);
        if ($housingUnit->is_occupied) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => [
                    'housing_unit_id' => ['This housing unit is already occupied.']
                ]
            ], 422);
        }
        
        $occupier = Occupier::create($request->all());
        
        // Mark housing unit as occupied
        $housingUnit->markAsOccupied();
        
        AuditLog::logActivity('create', $occupier, null, $occupier->toArray(), "Created occupier via API: {$occupier->name}");
        
        return response()->json([
            'message' => 'Occupier created successfully.',
            'occupier' => $occupier->load('housingUnit'),
        ], 201);
    }
    
    public function update(Request $request, Occupier $occupier)
    {
        $request->validate([
            'housing_unit_id' => ['required', 'exists:housing_units,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'move_in_date' => ['required', 'date'],
            'move_out_date' => ['nullable', 'date', 'after:move_in_date'],
            'lease_start_date' => ['required', 'date'],
            'lease_end_date' => ['required', 'date', 'after:lease_start_date'],
            'rental_amount' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'is_active' => ['boolean'],
        ]);
        
        // Check if housing unit is available (if changing units)
        if ($request->housing_unit_id != $occupier->housing_unit_id) {
            $newHousingUnit = HousingUnit::find($request->housing_unit_id);
            if ($newHousingUnit->is_occupied) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors' => [
                        'housing_unit_id' => ['This housing unit is already occupied.']
                    ]
                ], 422);
            }
        }
        
        $oldData = $occupier->toArray();
        $oldHousingUnitId = $occupier->housing_unit_id;
        
        $occupier->update($request->all());
        
        // Update housing unit occupancy status
        if ($request->housing_unit_id != $oldHousingUnitId) {
            // Mark old unit as vacant
            $oldHousingUnit = HousingUnit::find($oldHousingUnitId);
            $oldHousingUnit->markAsVacant();
            
            // Mark new unit as occupied
            $newHousingUnit = HousingUnit::find($request->housing_unit_id);
            $newHousingUnit->markAsOccupied();
        }
        
        // Update occupancy status based on move_out_date
        if ($request->filled('move_out_date')) {
            $occupier->housingUnit->markAsVacant();
        } else {
            $occupier->housingUnit->markAsOccupied();
        }
        
        AuditLog::logActivity('update', $occupier, $oldData, $occupier->toArray(), "Updated occupier via API: {$occupier->name}");
        
        return response()->json([
            'message' => 'Occupier updated successfully.',
            'occupier' => $occupier->load('housingUnit'),
        ], 200);
    }
    
    public function destroy(Occupier $occupier)
    {
        $oldData = $occupier->toArray();
        
        // Mark housing unit as vacant
        $occupier->housingUnit->markAsVacant();
        
        $occupier->delete();
        
        AuditLog::logActivity('delete', $occupier, $oldData, null, "Deleted occupier via API: {$occupier->name}");
        
        return response()->json([
            'message' => 'Occupier deleted successfully.',
        ], 200);
    }
}