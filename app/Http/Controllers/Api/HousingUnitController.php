<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HousingUnit;
use App\Models\AuditLog;

class HousingUnitController extends Controller
{
    public function index(Request $request)
    {
        $query = HousingUnit::with(['housingType', 'currentOccupier']);

        // Search and filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('unit_number', 'like', "%{$search}%")
                  ->orWhere('property_address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('housing_type_id', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_occupied', $request->status === 'occupied');
        }

        if ($request->filled('condition')) {
            $query->where('condition_grade', $request->condition);
        }

        $units = $query->paginate(10);

        return response()->json($units);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_number' => 'required|string|unique:housing_units,unit_number',
            'housing_type_id' => 'required|exists:housing_types,id',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'square_footage' => 'nullable|numeric|min:0',
            'parking_spaces' => 'required|integer|min:0',
            'rental_rate' => 'required|numeric|min:0',
            'condition_grade' => 'required|in:A,B,C,D,F',
            'property_address' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $unit = HousingUnit::create($validated);

        // Log the creation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'related_type' => 'housing_unit',
            'related_id' => $unit->id,
            'description' => "Created housing unit: {$unit->unit_number}",
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Housing unit created successfully',
            'unit' => $unit->load('housingType'),
        ], 201);
    }

    public function show(HousingUnit $housingUnit)
    {
        return response()->json($housingUnit->load(['housingType', 'currentOccupier', 'notes.user']));
    }

    public function update(Request $request, HousingUnit $housingUnit)
    {
        $validated = $request->validate([
            'unit_number' => 'required|string|unique:housing_units,unit_number,' . $housingUnit->id,
            'housing_type_id' => 'required|exists:housing_types,id',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'square_footage' => 'nullable|numeric|min:0',
            'parking_spaces' => 'required|integer|min:0',
            'rental_rate' => 'required|numeric|min:0',
            'condition_grade' => 'required|in:A,B,C,D,F',
            'property_address' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $housingUnit->toArray();

        $housingUnit->update($validated);

        // Log the update
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'related_type' => 'housing_unit',
            'related_id' => $housingUnit->id,
            'description' => "Updated housing unit: {$housingUnit->unit_number}",
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Housing unit updated successfully',
            'unit' => $housingUnit->load('housingType'),
        ]);
    }

    public function destroy(HousingUnit $housingUnit)
    {
        // Don't allow deletion if occupied
        if ($housingUnit->is_occupied) {
            return response()->json([
                'message' => 'Cannot delete occupied housing unit',
            ], 400);
        }

        // Log the deletion
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'related_type' => 'housing_unit',
            'related_id' => $housingUnit->id,
            'description' => "Deleted housing unit: {$housingUnit->unit_number}",
            'old_values' => $housingUnit->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $housingUnit->delete();

        return response()->json([
            'message' => 'Housing unit deleted successfully',
        ]);
    }
}