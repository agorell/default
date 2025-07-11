<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousingUnit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_number',
        'housing_type_id',
        'bedrooms',
        'bathrooms',
        'square_footage',
        'parking_spaces',
        'rental_rate',
        'is_occupied',
        'condition_grade',
        'property_address',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_occupied' => 'boolean',
        'is_active' => 'boolean',
        'rental_rate' => 'decimal:2',
        'square_footage' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'parking_spaces' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    public function housingType()
    {
        return $this->belongsTo(HousingType::class);
    }

    public function occupier()
    {
        return $this->hasOne(Occupier::class);
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

    public function scopeOccupied($query)
    {
        return $query->where('is_occupied', true);
    }

    public function scopeVacant($query)
    {
        return $query->where('is_occupied', false);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('housing_type_id', $typeId);
    }

    public function scopeByCondition($query, $condition)
    {
        return $query->where('condition_grade', $condition);
    }

    public function scopeByRentalRange($query, $minRent, $maxRent)
    {
        return $query->whereBetween('rental_rate', [$minRent, $maxRent]);
    }

    public function getFullAddressAttribute()
    {
        return $this->property_address . ' - Unit ' . $this->unit_number;
    }

    public function getStatusAttribute()
    {
        return $this->is_occupied ? 'Occupied' : 'Vacant';
    }

    public function getConditionAttribute()
    {
        $grades = [
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Fair',
            'D' => 'Poor',
            'F' => 'Critical'
        ];
        
        return $grades[$this->condition_grade] ?? 'Unknown';
    }

    public function getCurrentOccupier()
    {
        return $this->occupier()->whereNull('move_out_date')->first();
    }

    public function markAsOccupied()
    {
        $this->update(['is_occupied' => true]);
    }

    public function markAsVacant()
    {
        $this->update(['is_occupied' => false]);
    }

    public function getMonthlyIncomeAttribute()
    {
        return $this->rental_rate;
    }

    public function getYearlyIncomeAttribute()
    {
        return $this->rental_rate * 12;
    }
}