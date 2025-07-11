<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\HousingUnit;
use App\Models\Occupier;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        
        // User filter
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Privacy filter
        if ($request->filled('privacy')) {
            if ($request->privacy === 'public') {
                $query->where('is_private', false);
            } elseif ($request->privacy === 'private') {
                $query->where('is_private', true);
            }
        }
        
        // Show only user's private notes unless admin
        if (!auth()->user()->isAdmin()) {
            $query->where(function($q) {
                $q->where('is_private', false)
                  ->orWhere('user_id', auth()->id());
            });
        }
        
        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortField, ['title', 'category', 'priority', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $notes = $query->paginate(25);
        
        // Get filter options
        $categories = ['general', 'maintenance', 'complaint', 'inspection', 'lease', 'payment', 'communication', 'other'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $housingUnits = HousingUnit::active()->get();
        $occupiers = Occupier::current()->get();
        
        // Statistics
        $stats = [
            'total' => Note::count(),
            'recent' => Note::where('created_at', '>=', now()->subDays(30))->count(),
            'high_priority' => Note::whereIn('priority', ['high', 'urgent'])->count(),
            'my_notes' => Note::where('user_id', auth()->id())->count(),
        ];
        
        AuditLog::logActivity('view', new Note(), null, null, 'Viewed notes listing');
        
        return view('notes.index', compact(
            'notes', 
            'categories', 
            'priorities', 
            'housingUnits', 
            'occupiers', 
            'stats'
        ));
    }
    
    public function show(Note $note)
    {
        // Check if user can view this note
        if ($note->is_private && $note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to view this private note.');
        }
        
        $note->load(['user', 'housingUnit.housingType', 'occupier']);
        
        AuditLog::logActivity('view', $note, null, null, "Viewed note: {$note->title}");
        
        return view('notes.show', compact('note'));
    }
    
    public function create(Request $request)
    {
        $housingUnits = HousingUnit::active()->get();
        $occupiers = Occupier::current()->get();
        $categories = ['general', 'maintenance', 'complaint', 'inspection', 'lease', 'payment', 'communication', 'other'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        
        // Pre-select housing unit or occupier if provided
        $selectedHousingUnit = $request->housing_unit_id;
        $selectedOccupier = $request->occupier_id;
        
        return view('notes.create', compact(
            'housingUnits', 
            'occupiers', 
            'categories', 
            'priorities',
            'selectedHousingUnit',
            'selectedOccupier'
        ));
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
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt'],
        ]);
        
        $noteData = $request->except('attachments');
        $noteData['user_id'] = auth()->id();
        
        $note = Note::create($noteData);
        
        // Handle file attachments
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('note-attachments', 'public');
                $attachments[] = [
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toDateTimeString()
                ];
            }
            $note->update(['attachments' => $attachments]);
        }
        
        AuditLog::logActivity('create', $note, null, $note->toArray(), "Created note: {$note->title}");
        
        return redirect()->route('notes.index')
            ->with('success', 'Note created successfully.');
    }
    
    public function edit(Note $note)
    {
        // Check if user can edit this note
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to edit this note.');
        }
        
        $housingUnits = HousingUnit::active()->get();
        $occupiers = Occupier::current()->get();
        $categories = ['general', 'maintenance', 'complaint', 'inspection', 'lease', 'payment', 'communication', 'other'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        
        return view('notes.edit', compact('note', 'housingUnits', 'occupiers', 'categories', 'priorities'));
    }
    
    public function update(Request $request, Note $note)
    {
        // Check if user can edit this note
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to edit this note.');
        }
        
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'in:general,maintenance,complaint,inspection,lease,payment,communication,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'housing_unit_id' => ['nullable', 'exists:housing_units,id'],
            'occupier_id' => ['nullable', 'exists:occupiers,id'],
            'is_private' => ['boolean'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt'],
        ]);
        
        $oldData = $note->toArray();
        $noteData = $request->except('attachments');
        
        $note->update($noteData);
        
        // Handle file attachments
        if ($request->hasFile('attachments')) {
            $currentAttachments = $note->attachments ?? [];
            
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('note-attachments', 'public');
                $currentAttachments[] = [
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toDateTimeString()
                ];
            }
            
            $note->update(['attachments' => $currentAttachments]);
        }
        
        AuditLog::logActivity('update', $note, $oldData, $note->toArray(), "Updated note: {$note->title}");
        
        return redirect()->route('notes.index')
            ->with('success', 'Note updated successfully.');
    }
    
    public function destroy(Note $note)
    {
        // Check if user can delete this note
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to delete this note.');
        }
        
        $oldData = $note->toArray();
        
        // Delete associated files
        if ($note->attachments) {
            foreach ($note->attachments as $attachment) {
                if (isset($attachment['file_path'])) {
                    Storage::disk('public')->delete($attachment['file_path']);
                }
            }
        }
        
        $note->delete();
        
        AuditLog::logActivity('delete', $note, $oldData, null, "Deleted note: {$note->title}");
        
        return redirect()->route('notes.index')
            ->with('success', 'Note deleted successfully.');
    }
    
    public function removeAttachment(Note $note, $index)
    {
        // Check if user can edit this note
        if ($note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to edit this note.');
        }
        
        $attachments = $note->attachments ?? [];
        
        if (isset($attachments[$index])) {
            // Delete the file
            if (isset($attachments[$index]['file_path'])) {
                Storage::disk('public')->delete($attachments[$index]['file_path']);
            }
            
            // Remove from array
            unset($attachments[$index]);
            $note->update(['attachments' => array_values($attachments)]);
            
            AuditLog::logActivity('update', $note, null, null, "Removed attachment from note: {$note->title}");
        }
        
        return redirect()->route('notes.show', $note)
            ->with('success', 'Attachment removed successfully.');
    }
    
    public function downloadAttachment(Note $note, $index)
    {
        // Check if user can view this note
        if ($note->is_private && $note->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to access this note.');
        }
        
        $attachments = $note->attachments ?? [];
        
        if (!isset($attachments[$index]) || !isset($attachments[$index]['file_path'])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$index];
        $filePath = storage_path('app/public/' . $attachment['file_path']);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }
        
        AuditLog::logActivity('view', $note, null, null, "Downloaded attachment from note: {$note->title}");
        
        return response()->download($filePath, $attachment['original_name'] ?? 'attachment');
    }
}