<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'category',
        'priority',
        'user_id',
        'housing_unit_id',
        'occupier_id',
        'is_private',
        'attachment_path',
        'attachment_name',
        'is_active',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'is_active' => 'boolean',
    ];

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
        return $this->hasMany(AuditLog::class, 'related_id')->where('related_type', 'note');
    }

    public function getPriorityTextAttribute()
    {
        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];

        return $priorities[$this->priority] ?? 'Unknown';
    }

    public function getCategoryTextAttribute()
    {
        $categories = [
            'general' => 'General',
            'maintenance' => 'Maintenance',
            'complaint' => 'Complaint',
            'inquiry' => 'Inquiry',
            'lease' => 'Lease',
            'payment' => 'Payment',
            'inspection' => 'Inspection',
            'other' => 'Other'
        ];

        return $categories[$this->category] ?? 'Unknown';
    }
}