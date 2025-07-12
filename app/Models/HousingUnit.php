<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousingUnit extends Model
{
    use HasFactory;

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

    public function housingType()
    {
        return $this->belongsTo(HousingType::class);
    }

    public function occupiers()
    {
        return $this->hasMany(Occupier::class);
    }

    public function currentOccupier()
    {
        return $this->hasOne(Occupier::class)->where('is_current', true);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'related_id')->where('related_type', 'housing_unit');
    }

    public function getStatusAttribute()
    {
        return $this->is_occupied ? 'Occupied' : 'Vacant';
    }

    public function getConditionTextAttribute()
    {
        $conditions = [
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Fair',
            'D' => 'Poor',
            'F' => 'Needs Major Repairs'
        ];

        return $conditions[$this->condition_grade] ?? 'Unknown';
    }
}