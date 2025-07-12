<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupier extends Model
{
    use HasFactory;

    protected $fillable = [
        'housing_unit_id',
        'name',
        'email',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'occupancy_start_date',
        'occupancy_end_date',
        'lease_terms',
        'is_current',
        'monthly_rent',
        'security_deposit',
        'notes',
    ];

    protected $casts = [
        'occupancy_start_date' => 'date',
        'occupancy_end_date' => 'date',
        'is_current' => 'boolean',
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
    ];

    public function housingUnit()
    {
        return $this->belongsTo(HousingUnit::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'related_id')->where('related_type', 'occupier');
    }

    public function getOccupancyDurationAttribute()
    {
        if (!$this->occupancy_start_date) {
            return null;
        }

        $endDate = $this->occupancy_end_date ?? now();
        return $this->occupancy_start_date->diffInDays($endDate);
    }

    public function getStatusAttribute()
    {
        return $this->is_current ? 'Current' : 'Former';
    }
}