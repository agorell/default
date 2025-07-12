<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;
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

        return response()->json($notes);
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
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_private'] = $validated['is_private'] ?? false;

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

        return response()->json([
            'message' => 'Note created successfully',
            'note' => $note->load(['user', 'housingUnit', 'occupier']),
        ], 201);
    }

    public function show(Note $note)
    {
        return response()->json($note->load(['user', 'housingUnit', 'occupier']));
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
        ]);

        $oldValues = $note->toArray();

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

        return response()->json([
            'message' => 'Note updated successfully',
            'note' => $note->load(['user', 'housingUnit', 'occupier']),
        ]);
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

        return response()->json([
            'message' => 'Note deleted successfully',
        ]);
    }
}