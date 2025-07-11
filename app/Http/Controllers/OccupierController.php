<?php

namespace App\Http\Controllers;

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
        
        // Date range filter
        if ($request->filled('move_in_from')) {
            $query->where('move_in_date', '>=', $request->move_in_from);
        }
        if ($request->filled('move_in_to')) {
            $query->where('move_in_date', '<=', $request->move_in_to);
        }
        
        // Sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortField, ['name', 'move_in_date', 'lease_end_date', 'rental_amount', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }
        
        $occupiers = $query->paginate(25);
        
        // Get filter options
        $housingUnits = HousingUnit::active()->get();
        
        // Statistics
        $stats = [
            'total' => Occupier::count(),
            'active' => Occupier::current()->count(),
            'inactive' => Occupier::where('is_active', false)->count(),
            'expiring_soon' => Occupier::where('is_active', true)
                ->where('lease_end_date', '>=', Carbon::now())
                ->where('lease_end_date', '<=', Carbon::now()->addDays(30))
                ->count(),
            'total_monthly_rent' => Occupier::current()->sum('rental_amount'),
        ];
        
        AuditLog::logActivity('view', new Occupier(), null, null, 'Viewed occupiers listing');
        
        return view('occupiers.index', compact('occupiers', 'housingUnits', 'stats'));
    }
    
    public function show(Occupier $occupier)
    {
        $occupier->load(['housingUnit.housingType', 'notes.user']);
        
        // Get occupier statistics
        $stats = [
            'total_notes' => $occupier->notes()->count(),
            'recent_notes' => $occupier->notes()->where('created_at', '>=', now()->subDays(30))->count(),
            'occupancy_duration' => $occupier->getOccupancyDurationAttribute(),
            'lease_status' => $occupier->getLeaseStatusAttribute(),
            'days_until_lease_expiry' => $occupier->getDaysUntilLeaseExpiryAttribute(),
            'total_rent_paid' => $occupier->getTotalRentPaidAttribute(),
        ];
        
        AuditLog::logActivity('view', $occupier, null, null, "Viewed occupier: {$occupier->name}");
        
        return view('occupiers.show', compact('occupier', 'stats'));
    }
    
    public function create()
    {
        $housingUnits = HousingUnit::active()->vacant()->get();
        
        if ($housingUnits->isEmpty()) {
            return redirect()->route('occupiers.index')
                ->with('error', 'No vacant housing units available for new occupiers.');
        }
        
        return view('occupiers.create', compact('housingUnits'));
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
            return redirect()->back()
                ->withErrors(['housing_unit_id' => 'This housing unit is already occupied.'])
                ->withInput();
        }
        
        $occupier = Occupier::create($request->all());
        
        // Mark housing unit as occupied
        $housingUnit->markAsOccupied();
        
        AuditLog::logActivity('create', $occupier, null, $occupier->toArray(), "Created occupier: {$occupier->name}");
        
        return redirect()->route('occupiers.index')
            ->with('success', 'Occupier created successfully.');
    }
    
    public function edit(Occupier $occupier)
    {
        $housingUnits = HousingUnit::active()->get();
        
        return view('occupiers.edit', compact('occupier', 'housingUnits'));
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
                return redirect()->back()
                    ->withErrors(['housing_unit_id' => 'This housing unit is already occupied.'])
                    ->withInput();
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
        
        AuditLog::logActivity('update', $occupier, $oldData, $occupier->toArray(), "Updated occupier: {$occupier->name}");
        
        return redirect()->route('occupiers.index')
            ->with('success', 'Occupier updated successfully.');
    }
    
    public function destroy(Occupier $occupier)
    {
        $oldData = $occupier->toArray();
        
        // Mark housing unit as vacant
        $occupier->housingUnit->markAsVacant();
        
        $occupier->delete();
        
        AuditLog::logActivity('delete', $occupier, $oldData, null, "Deleted occupier: {$occupier->name}");
        
        return redirect()->route('occupiers.index')
            ->with('success', 'Occupier deleted successfully.');
    }
    
    public function moveOut(Request $request, Occupier $occupier)
    {
        $request->validate([
            'move_out_date' => ['required', 'date', 'after:move_in_date'],
        ]);
        
        $oldData = $occupier->toArray();
        
        $occupier->moveOut($request->move_out_date);
        
        AuditLog::logActivity('update', $occupier, $oldData, $occupier->toArray(), "Moved out occupier: {$occupier->name}");
        
        return redirect()->route('occupiers.index')
            ->with('success', 'Occupier moved out successfully.');
    }
    
    public function renewLease(Request $request, Occupier $occupier)
    {
        $request->validate([
            'lease_end_date' => ['required', 'date', 'after:today'],
            'rental_amount' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
        ]);
        
        $oldData = $occupier->toArray();
        
        $occupier->update([
            'lease_end_date' => $request->lease_end_date,
            'rental_amount' => $request->rental_amount ?? $occupier->rental_amount,
        ]);
        
        AuditLog::logActivity('update', $occupier, $oldData, $occupier->toArray(), "Renewed lease for occupier: {$occupier->name}");
        
        return redirect()->route('occupiers.show', $occupier)
            ->with('success', 'Lease renewed successfully.');
    }
}