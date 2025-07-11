<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HousingType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    public function housingUnits()
    {
        return $this->hasMany(HousingUnit::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getUnitsCountAttribute()
    {
        return $this->housingUnits()->count();
    }

    public function getOccupiedUnitsCountAttribute()
    {
        return $this->housingUnits()->where('is_occupied', true)->count();
    }

    public function getVacantUnitsCountAttribute()
    {
        return $this->housingUnits()->where('is_occupied', false)->count();
    }

    public function getOccupancyRateAttribute()
    {
        $totalUnits = $this->getUnitsCountAttribute();
        if ($totalUnits === 0) {
            return 0;
        }
        
        return round(($this->getOccupiedUnitsCountAttribute() / $totalUnits) * 100, 2);
    }

    public function getAverageRentalRateAttribute()
    {
        return $this->housingUnits()->avg('rental_rate') ?? 0;
    }

    public function getTotalMonthlyIncomeAttribute()
    {
        return $this->housingUnits()->where('is_occupied', true)->sum('rental_rate');
    }

    public function getTotalYearlyIncomeAttribute()
    {
        return $this->getTotalMonthlyIncomeAttribute() * 12;
    }
}