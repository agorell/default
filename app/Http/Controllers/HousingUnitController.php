<?php

namespace App\Http\Controllers;

use App\Models\HousingUnit;
use App\Models\HousingType;
use App\Models\Occupier;
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
            if ($request->condition === 'poor') {
                $query->whereIn('condition_grade', ['D', 'F']);
            } else {
                $query->where('condition_grade', $request->condition);
            }
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
        
        // Sorting
        $sortField = $request->get('sort', 'unit_number');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortField, ['unit_number', 'rental_rate', 'bedrooms', 'condition_grade', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('unit_number', 'asc');
        }
        
        $housingUnits = $query->paginate(25);
        
        // Get filter options
        $housingTypes = HousingType::active()->get();
        $conditionGrades = ['A', 'B', 'C', 'D', 'F'];
        $bedroomOptions = HousingUnit::distinct()->pluck('bedrooms')->sort()->values();
        
        // Statistics
        $stats = [
            'total' => HousingUnit::active()->count(),
            'occupied' => HousingUnit::active()->occupied()->count(),
            'vacant' => HousingUnit::active()->vacant()->count(),
            'average_rent' => HousingUnit::active()->avg('rental_rate'),
            'total_monthly_revenue' => HousingUnit::active()->occupied()->sum('rental_rate'),
        ];
        
        AuditLog::logActivity('view', new HousingUnit(), null, null, 'Viewed housing units listing');
        
        return view('housing-units.index', compact(
            'housingUnits', 
            'housingTypes', 
            'conditionGrades', 
            'bedroomOptions', 
            'stats'
        ));
    }
    
    public function show(HousingUnit $housingUnit)
    {
        $housingUnit->load(['housingType', 'occupier', 'notes.user']);
        
        // Get unit statistics
        $stats = [
            'total_notes' => $housingUnit->notes()->count(),
            'recent_notes' => $housingUnit->notes()->where('created_at', '>=', now()->subDays(30))->count(),
            'occupancy_history' => $housingUnit->occupiers()->count(),
            'monthly_income' => $housingUnit->rental_rate ?? 0,
            'yearly_income' => ($housingUnit->rental_rate ?? 0) * 12,
        ];
        
        AuditLog::logActivity('view', $housingUnit, null, null, "Viewed housing unit: {$housingUnit->unit_number}");
        
        return view('housing-units.show', compact('housingUnit', 'stats'));
    }
    
    public function create()
    {
        $housingTypes = HousingType::active()->get();
        $conditionGrades = [
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Fair',
            'D' => 'Poor',
            'F' => 'Critical'
        ];
        
        return view('housing-units.create', compact('housingTypes', 'conditionGrades'));
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
            return redirect()->back()
                ->withErrors(['unit_number' => 'A unit with this number already exists at this address.'])
                ->withInput();
        }
        
        $housingUnit = HousingUnit::create($request->all());
        
        AuditLog::logActivity('create', $housingUnit, null, $housingUnit->toArray(), "Created housing unit: {$housingUnit->unit_number}");
        
        return redirect()->route('housing-units.index')
            ->with('success', 'Housing unit created successfully.');
    }
    
    public function edit(HousingUnit $housingUnit)
    {
        $housingTypes = HousingType::active()->get();
        $conditionGrades = [
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Fair',
            'D' => 'Poor',
            'F' => 'Critical'
        ];
        
        return view('housing-units.edit', compact('housingUnit', 'housingTypes', 'conditionGrades'));
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
            return redirect()->back()
                ->withErrors(['unit_number' => 'A unit with this number already exists at this address.'])
                ->withInput();
        }
        
        $oldData = $housingUnit->toArray();
        $housingUnit->update($request->all());
        
        AuditLog::logActivity('update', $housingUnit, $oldData, $housingUnit->toArray(), "Updated housing unit: {$housingUnit->unit_number}");
        
        return redirect()->route('housing-units.index')
            ->with('success', 'Housing unit updated successfully.');
    }
    
    public function destroy(HousingUnit $housingUnit)
    {
        // Check if unit has an active occupier
        if ($housingUnit->occupier && $housingUnit->occupier->is_active) {
            return redirect()->route('housing-units.index')
                ->with('error', 'Cannot delete a housing unit with an active occupier.');
        }
        
        $oldData = $housingUnit->toArray();
        $housingUnit->delete();
        
        AuditLog::logActivity('delete', $housingUnit, $oldData, null, "Deleted housing unit: {$housingUnit->unit_number}");
        
        return redirect()->route('housing-units.index')
            ->with('success', 'Housing unit deleted successfully.');
    }
    
    public function toggleStatus(HousingUnit $housingUnit)
    {
        $oldData = $housingUnit->toArray();
        $housingUnit->is_active = !$housingUnit->is_active;
        $housingUnit->save();
        
        $status = $housingUnit->is_active ? 'activated' : 'deactivated';
        
        AuditLog::logActivity('update', $housingUnit, $oldData, $housingUnit->toArray(), "Housing unit {$status}: {$housingUnit->unit_number}");
        
        return redirect()->route('housing-units.index')
            ->with('success', "Housing unit {$status} successfully.");
    }
}