<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::with(['user', 'housingUnit', 'occupier']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Housing unit filter
        if ($request->filled('housing_unit')) {
            $query->where('housing_unit_id', $request->housing_unit);
        }
        
        // Occupier filter
        if ($request->filled('occupier')) {
            $query->where('occupier_id', $request->occupier);
        }
        
        // Show only user's private notes unless admin
        if (!auth()->user()->isAdmin()) {
            $query->where(function($q) {
                $q->where('is_private', false)
                  ->orWhere('user_id', auth()->id());
            });
        }
        
        // Pagination
        $perPage = $request->get('per_page', 25);
        $notes = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        AuditLog::logActivity('view', new Note(), null, null, 'Viewed notes via API');
        
        return response()->json([
            'notes' => $notes->items(),
            'pagination' => [
                'current_page' => $notes->currentPage(),
                'last_page' => $notes->lastPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
            ],
            'statistics' => [
                'total' => Note::count(),
                'recent' => Note::where('created_at', '>=', now()->subDays(30))->count(),
                'high_priority' => Note::whereIn('priority', ['high', 'urgent'])->count(),
                'my_notes' => Note::where('user_id', auth()->id())->count(),
            ],
        ], 200);
    }
    
    public function show(Note $note)
    {
        // Check if user can view this note
        if ($note->is_private && $note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'You do not have permission to view this private note.',
            ], 403);
        }
        
        $note->load(['user', 'housingUnit.housingType', 'occupier']);
        
        AuditLog::logActivity('view', $note, null, null, "Viewed note via API: {$note->title}");
        
        return response()->json([
            'note' => $note,
        ], 200);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'in:general,maintenance,complaint,inspection,lease,payment,communication,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'housing_unit_id' => ['nullable', 'exists:housing_units,id'],
            'occupier_id' => ['nullable', 'exists:occupiers,id'],
            'is_private' => ['boolean'],
        ]);
        
        $noteData = $request->all();
        $noteData['user_id'] = auth()->id();
        
        $note = Note::create($noteData);
        
        AuditLog::logActivity('create', $note, null, $note->toArray(), "Created note via API: {$note->title}");
        
        return response()->json([
            'message' => 'Note created successfully.',
            'note' => $note->load(['user', 'housingUnit', 'occupier']),
        ], 201);
    }
    
    public function update(Request $request, Note $note)
    {
        // Check if user can edit this note
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'You do not have permission to edit this note.',
            ], 403);
        }
        
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'in:general,maintenance,complaint,inspection,lease,payment,communication,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'housing_unit_id' => ['nullable', 'exists:housing_units,id'],
            'occupier_id' => ['nullable', 'exists:occupiers,id'],
            'is_private' => ['boolean'],
        ]);
        
        $oldData = $note->toArray();
        $note->update($request->all());
        
        AuditLog::logActivity('update', $note, $oldData, $note->toArray(), "Updated note via API: {$note->title}");
        
        return response()->json([
            'message' => 'Note updated successfully.',
            'note' => $note->load(['user', 'housingUnit', 'occupier']),
        ], 200);
    }
    
    public function destroy(Note $note)
    {
        // Check if user can delete this note
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'You do not have permission to delete this note.',
            ], 403);
        }
        
        $oldData = $note->toArray();
        $note->delete();
        
        AuditLog::logActivity('delete', $note, $oldData, null, "Deleted note via API: {$note->title}");
        
        return response()->json([
            'message' => 'Note deleted successfully.',
        ], 200);
    }
}