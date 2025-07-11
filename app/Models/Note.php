<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'housing_unit_id',
        'occupier_id',
        'title',
        'content',
        'category',
        'priority',
        'is_private',
        'attachments',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'attachments' => 'array',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function housingUnit()
    {
        return $this->belongsTo(HousingUnit::class);
    }

    public function occupier()
    {
        return $this->belongsTo(Occupier::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByHousingUnit($query, $unitId)
    {
        return $query->where('housing_unit_id', $unitId);
    }

    public function scopeByOccupier($query, $occupierId)
    {
        return $query->where('occupier_id', $occupierId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function getShortContentAttribute()
    {
        return strlen($this->content) > 100 ? substr($this->content, 0, 100) . '...' : $this->content;
    }

    public function getPriorityLabelAttribute()
    {
        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];
        
        return $priorities[$this->priority] ?? 'Unknown';
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger'
        ];
        
        return $colors[$this->priority] ?? 'secondary';
    }

    public function getCategoryLabelAttribute()
    {
        $categories = [
            'general' => 'General',
            'maintenance' => 'Maintenance',
            'complaint' => 'Complaint',
            'inspection' => 'Inspection',
            'lease' => 'Lease',
            'payment' => 'Payment',
            'communication' => 'Communication',
            'other' => 'Other'
        ];
        
        return $categories[$this->category] ?? 'Unknown';
    }

    public function hasAttachments()
    {
        return is_array($this->attachments) && count($this->attachments) > 0;
    }

    public function getAttachmentCount()
    {
        return is_array($this->attachments) ? count($this->attachments) : 0;
    }

    public function addAttachment($filePath, $originalName = null)
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = [
            'file_path' => $filePath,
            'original_name' => $originalName ?? basename($filePath),
            'uploaded_at' => now()->toDateTimeString()
        ];
        
        $this->update(['attachments' => $attachments]);
    }

    public function removeAttachment($index)
    {
        $attachments = $this->attachments ?? [];
        if (isset($attachments[$index])) {
            unset($attachments[$index]);
            $this->update(['attachments' => array_values($attachments)]);
        }
    }

    public function getRelatedEntityAttribute()
    {
        if ($this->housing_unit_id) {
            return $this->housingUnit;
        }
        
        if ($this->occupier_id) {
            return $this->occupier;
        }
        
        return null;
    }

    public function getRelatedEntityTypeAttribute()
    {
        if ($this->housing_unit_id) {
            return 'Housing Unit';
        }
        
        if ($this->occupier_id) {
            return 'Occupier';
        }
        
        return 'General';
    }
}