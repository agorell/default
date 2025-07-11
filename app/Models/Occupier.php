<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occupier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'housing_unit_id',
        'name',
        'email',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'move_in_date',
        'move_out_date',
        'lease_start_date',
        'lease_end_date',
        'rental_amount',
        'deposit_amount',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'move_in_date' => 'date',
        'move_out_date' => 'date',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'rental_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    protected $dates = ['deleted_at'];

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
        return $this->hasMany(AuditLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)->whereNull('move_out_date');
    }

    public function scopeByUnit($query, $unitId)
    {
        return $query->where('housing_unit_id', $unitId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('move_in_date', [$startDate, $endDate]);
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getLeaseStatusAttribute()
    {
        if (!$this->lease_start_date || !$this->lease_end_date) {
            return 'No Lease';
        }
        
        $now = now();
        if ($now < $this->lease_start_date) {
            return 'Future Lease';
        }
        
        if ($now > $this->lease_end_date) {
            return 'Expired Lease';
        }
        
        return 'Active Lease';
    }

    public function getOccupancyDurationAttribute()
    {
        if (!$this->move_in_date) {
            return 'N/A';
        }
        
        $endDate = $this->move_out_date ?? now();
        $duration = $this->move_in_date->diffInDays($endDate);
        
        if ($duration < 30) {
            return $duration . ' days';
        } elseif ($duration < 365) {
            return floor($duration / 30) . ' months';
        } else {
            return floor($duration / 365) . ' years';
        }
    }

    public function getDaysUntilLeaseExpiryAttribute()
    {
        if (!$this->lease_end_date) {
            return null;
        }
        
        $now = now();
        if ($now > $this->lease_end_date) {
            return 0; // Expired
        }
        
        return $now->diffInDays($this->lease_end_date);
    }

    public function isLeaseExpiringSoon($days = 30)
    {
        $daysUntilExpiry = $this->getDaysUntilLeaseExpiryAttribute();
        return $daysUntilExpiry !== null && $daysUntilExpiry <= $days && $daysUntilExpiry > 0;
    }

    public function isLeaseExpired()
    {
        return $this->getDaysUntilLeaseExpiryAttribute() === 0;
    }

    public function isCurrentlyOccupied()
    {
        return $this->is_active && !$this->move_out_date;
    }

    public function getTotalRentPaidAttribute()
    {
        if (!$this->move_in_date) {
            return 0;
        }
        
        $endDate = $this->move_out_date ?? now();
        $monthsOccupied = $this->move_in_date->diffInMonths($endDate);
        
        return $monthsOccupied * $this->rental_amount;
    }

    public function moveOut($moveOutDate = null)
    {
        $this->update([
            'move_out_date' => $moveOutDate ?? now(),
            'is_active' => false
        ]);
        
        // Mark housing unit as vacant
        $this->housingUnit->markAsVacant();
    }

    public function moveIn($moveInDate = null)
    {
        $this->update([
            'move_in_date' => $moveInDate ?? now(),
            'is_active' => true,
            'move_out_date' => null
        ]);
        
        // Mark housing unit as occupied
        $this->housingUnit->markAsOccupied();
    }
}