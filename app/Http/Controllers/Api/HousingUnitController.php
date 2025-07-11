<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HousingUnit;
use App\Models\HousingType;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class HousingUnitController extends Controller
{
    public function index(Request $request)
    {
        $query = HousingUnit::with(['housingType', 'occupier']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('unit_number', 'like', "%{$search}%")
                  ->orWhere('property_address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Housing type filter
        if ($request->filled('type')) {
            $query->where('housing_type_id', $request->type);
        }
        
        // Occupancy filter
        if ($request->filled('occupancy')) {
            $query->where('is_occupied', $request->occupancy === 'occupied');
        }
        
        // Condition filter
        if ($request->filled('condition')) {
            $query->where('condition_grade', $request->condition);
        }
        
        // Bedrooms filter
        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }
        
        // Rent range filter
        if ($request->filled('min_rent')) {
            $query->where('rental_rate', '>=', $request->min_rent);
        }
        if ($request->filled('max_rent')) {
            $query->where('rental_rate', '<=', $request->max_rent);
        }
        
        // Pagination
        $perPage = $request->get('per_page', 25);
        $housingUnits = $query->paginate($perPage);
        
        AuditLog::logActivity('view', new HousingUnit(), null, null, 'Viewed housing units via API');
        
        return response()->json([
            'housing_units' => $housingUnits->items(),
            'pagination' => [
                'current_page' => $housingUnits->currentPage(),
                'last_page' => $housingUnits->lastPage(),
                'per_page' => $housingUnits->perPage(),
                'total' => $housingUnits->total(),
            ],
            'statistics' => [
                'total' => HousingUnit::active()->count(),
                'occupied' => HousingUnit::active()->occupied()->count(),
                'vacant' => HousingUnit::active()->vacant()->count(),
                'average_rent' => HousingUnit::active()->avg('rental_rate'),
                'total_monthly_revenue' => HousingUnit::active()->occupied()->sum('rental_rate'),
            ],
        ], 200);
    }
    
    public function show(HousingUnit $housingUnit)
    {
        $housingUnit->load(['housingType', 'occupier', 'notes.user']);
        
        AuditLog::logActivity('view', $housingUnit, null, null, "Viewed housing unit via API: {$housingUnit->unit_number}");
        
        return response()->json([
            'housing_unit' => $housingUnit,
            'statistics' => [
                'total_notes' => $housingUnit->notes()->count(),
                'recent_notes' => $housingUnit->notes()->where('created_at', '>=', now()->subDays(30))->count(),
                'monthly_income' => $housingUnit->rental_rate ?? 0,
                'yearly_income' => ($housingUnit->rental_rate ?? 0) * 12,
            ],
        ], 200);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'unit_number' => ['required', 'string', 'max:50'],
            'housing_type_id' => ['required', 'exists:housing_types,id'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'square_footage' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'parking_spaces' => ['required', 'integer', 'min:0', 'max:10'],
            'rental_rate' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'condition_grade' => ['required', 'in:A,B,C,D,F'],
            'property_address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);
        
        // Check for duplicate unit number at same address
        $existingUnit = HousingUnit::where('unit_number', $request->unit_number)
            ->where('property_address', $request->property_address)
            ->first();
        
        if ($existingUnit) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => [
                    'unit_number' => ['A unit with this number already exists at this address.']
                ]
            ], 422);
        }
        
        $housingUnit = HousingUnit::create($request->all());
        
        AuditLog::logActivity('create', $housingUnit, null, $housingUnit->toArray(), "Created housing unit via API: {$housingUnit->unit_number}");
        
        return response()->json([
            'message' => 'Housing unit created successfully.',
            'housing_unit' => $housingUnit->load('housingType'),
        ], 201);
    }
    
    public function update(Request $request, HousingUnit $housingUnit)
    {
        $request->validate([
            'unit_number' => ['required', 'string', 'max:50'],
            'housing_type_id' => ['required', 'exists:housing_types,id'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'square_footage' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'parking_spaces' => ['required', 'integer', 'min:0', 'max:10'],
            'rental_rate' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'condition_grade' => ['required', 'in:A,B,C,D,F'],
            'property_address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);
        
        // Check for duplicate unit number at same address (excluding current unit)
        $existingUnit = HousingUnit::where('unit_number', $request->unit_number)
            ->where('property_address', $request->property_address)
            ->where('id', '!=', $housingUnit->id)
            ->first();
        
        if ($existingUnit) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => [
                    'unit_number' => ['A unit with this number already exists at this address.']
                ]
            ], 422);
        }
        
        $oldData = $housingUnit->toArray();
        $housingUnit->update($request->all());
        
        AuditLog::logActivity('update', $housingUnit, $oldData, $housingUnit->toArray(), "Updated housing unit via API: {$housingUnit->unit_number}");
        
        return response()->json([
            'message' => 'Housing unit updated successfully.',
            'housing_unit' => $housingUnit->load('housingType'),
        ], 200);
    }
    
    public function destroy(HousingUnit $housingUnit)
    {
        // Check if unit has an active occupier
        if ($housingUnit->occupier && $housingUnit->occupier->is_active) {
            return response()->json([
                'message' => 'Cannot delete a housing unit with an active occupier.',
            ], 403);
        }
        
        $oldData = $housingUnit->toArray();
        $housingUnit->delete();
        
        AuditLog::logActivity('delete', $housingUnit, $oldData, null, "Deleted housing unit via API: {$housingUnit->unit_number}");
        
        return response()->json([
            'message' => 'Housing unit deleted successfully.',
        ], 200);
    }
}