<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Occupier;
use App\Models\HousingUnit;
use App\Models\AuditLog;

class OccupierController extends Controller
{
    public function index(Request $request)
    {
        $query = Occupier::with(['housingUnit', 'housingUnit.housingType']);

        // Search and filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_current', $request->status === 'current');
        }

        if ($request->filled('unit')) {
            $query->where('housing_unit_id', $request->unit);
        }

        $occupiers = $query->paginate(10);
        $housingUnits = HousingUnit::where('is_active', true)->get();

        return view('occupiers.index', compact('occupiers', 'housingUnits'));
    }

    public function create()
    {
        $vacantUnits = HousingUnit::where('is_active', true)
            ->where('is_occupied', false)
            ->get();
        
        return view('occupiers.create', compact('vacantUnits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'housing_unit_id' => 'required|exists:housing_units,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'occupancy_start_date' => 'required|date',
            'lease_terms' => 'nullable|string',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['is_current'] = true;

        $occupier = Occupier::create($validated);

        // Update housing unit to occupied
        $housingUnit = HousingUnit::find($validated['housing_unit_id']);
        $housingUnit->update(['is_occupied' => true]);

        // Log the creation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'related_type' => 'occupier',
            'related_id' => $occupier->id,
            'description' => "Created occupier: {$occupier->name} for unit {$housingUnit->unit_number}",
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('occupiers.index')
            ->with('success', 'Occupier created successfully.');
    }

    public function show(Occupier $occupier)
    {
        $occupier->load(['housingUnit', 'housingUnit.housingType', 'notes.user']);
        return view('occupiers.show', compact('occupier'));
    }

    public function edit(Occupier $occupier)
    {
        $housingUnits = HousingUnit::where('is_active', true)->get();
        return view('occupiers.edit', compact('occupier', 'housingUnits'));
    }

    public function update(Request $request, Occupier $occupier)
    {
        $validated = $request->validate([
            'housing_unit_id' => 'required|exists:housing_units,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'occupancy_start_date' => 'required|date',
            'occupancy_end_date' => 'nullable|date|after:occupancy_start_date',
            'lease_terms' => 'nullable|string',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $occupier->toArray();
        $oldUnitId = $occupier->housing_unit_id;

        $occupier->update($validated);

        // If unit changed, update occupancy status
        if ($oldUnitId != $validated['housing_unit_id']) {
            // Mark old unit as vacant
            HousingUnit::find($oldUnitId)->update(['is_occupied' => false]);
            
            // Mark new unit as occupied
            HousingUnit::find($validated['housing_unit_id'])->update(['is_occupied' => true]);
        }

        // Log the update
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'related_type' => 'occupier',
            'related_id' => $occupier->id,
            'description' => "Updated occupier: {$occupier->name}",
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('occupiers.index')
            ->with('success', 'Occupier updated successfully.');
    }

    public function destroy(Occupier $occupier)
    {
        $housingUnit = $occupier->housingUnit;

        // Log the deletion
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'related_type' => 'occupier',
            'related_id' => $occupier->id,
            'description' => "Deleted occupier: {$occupier->name}",
            'old_values' => $occupier->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $occupier->delete();

        // Mark unit as vacant
        $housingUnit->update(['is_occupied' => false]);

        return redirect()->route('occupiers.index')
            ->with('success', 'Occupier deleted successfully.');
    }

    public function moveOut(Request $request, Occupier $occupier)
    {
        $validated = $request->validate([
            'occupancy_end_date' => 'required|date|after:occupancy_start_date',
        ]);

        $occupier->update([
            'occupancy_end_date' => $validated['occupancy_end_date'],
            'is_current' => false,
        ]);

        // Mark unit as vacant
        $occupier->housingUnit->update(['is_occupied' => false]);

        // Log the move out
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'related_type' => 'occupier',
            'related_id' => $occupier->id,
            'description' => "Moved out occupier: {$occupier->name}",
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('occupiers.index')
            ->with('success', 'Occupier moved out successfully.');
    }
}