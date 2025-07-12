<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\HousingUnit;
use App\Models\Occupier;
use App\Models\AuditLog;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::with(['user', 'housingUnit', 'occupier']);

        // Search and filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('unit')) {
            $query->where('housing_unit_id', $request->unit);
        }

        $notes = $query->orderBy('created_at', 'desc')->paginate(10);
        $housingUnits = HousingUnit::where('is_active', true)->get();

        return view('notes.index', compact('notes', 'housingUnits'));
    }

    public function create()
    {
        $housingUnits = HousingUnit::where('is_active', true)->get();
        $occupiers = Occupier::where('is_current', true)->get();
        
        return view('notes.create', compact('housingUnits', 'occupiers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,maintenance,complaint,inquiry,lease,payment,inspection,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'housing_unit_id' => 'nullable|exists:housing_units,id',
            'occupier_id' => 'nullable|exists:occupiers,id',
            'is_private' => 'boolean',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_private'] = $request->boolean('is_private', false);

        // Handle file attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('attachments', $filename, 'public');
            
            $validated['attachment_path'] = $path;
            $validated['attachment_name'] = $file->getClientOriginalName();
        }

        $note = Note::create($validated);

        // Log the creation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'related_type' => 'note',
            'related_id' => $note->id,
            'description' => "Created note: {$note->title}",
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('notes.index')
            ->with('success', 'Note created successfully.');
    }

    public function show(Note $note)
    {
        $note->load(['user', 'housingUnit', 'occupier']);
        return view('notes.show', compact('note'));
    }

    public function edit(Note $note)
    {
        $housingUnits = HousingUnit::where('is_active', true)->get();
        $occupiers = Occupier::where('is_current', true)->get();
        
        return view('notes.edit', compact('note', 'housingUnits', 'occupiers'));
    }

    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:general,maintenance,complaint,inquiry,lease,payment,inspection,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'housing_unit_id' => 'nullable|exists:housing_units,id',
            'occupier_id' => 'nullable|exists:occupiers,id',
            'is_private' => 'boolean',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $oldValues = $note->toArray();

        $validated['is_private'] = $request->boolean('is_private', false);

        // Handle file attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('attachments', $filename, 'public');
            
            $validated['attachment_path'] = $path;
            $validated['attachment_name'] = $file->getClientOriginalName();
        }

        $note->update($validated);

        // Log the update
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'related_type' => 'note',
            'related_id' => $note->id,
            'description' => "Updated note: {$note->title}",
            'old_values' => $oldValues,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('notes.index')
            ->with('success', 'Note updated successfully.');
    }

    public function destroy(Note $note)
    {
        // Log the deletion
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'related_type' => 'note',
            'related_id' => $note->id,
            'description' => "Deleted note: {$note->title}",
            'old_values' => $note->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $note->delete();

        return redirect()->route('notes.index')
            ->with('success', 'Note deleted successfully.');
    }
}