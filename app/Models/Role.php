<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }
        
        if (is_array($permission)) {
            return $this->permissions()->whereIn('name', $permission)->exists();
        }
        
        return false;
    }

    public function givePermission($permission)
    {
        if (is_string($permission)) {
            $permissionModel = Permission::where('name', $permission)->first();
            if ($permissionModel && !$this->permissions()->where('permission_id', $permissionModel->id)->exists()) {
                $this->permissions()->attach($permissionModel->id);
            }
        }
        
        if (is_array($permission)) {
            $permissionModels = Permission::whereIn('name', $permission)->get();
            foreach ($permissionModels as $permissionModel) {
                if (!$this->permissions()->where('permission_id', $permissionModel->id)->exists()) {
                    $this->permissions()->attach($permissionModel->id);
                }
            }
        }
    }

    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permissionModel = Permission::where('name', $permission)->first();
            if ($permissionModel) {
                $this->permissions()->detach($permissionModel->id);
            }
        }
        
        if (is_array($permission)) {
            $permissionModels = Permission::whereIn('name', $permission)->get();
            foreach ($permissionModels as $permissionModel) {
                $this->permissions()->detach($permissionModel->id);
            }
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getPermissionNamesAttribute()
    {
        return $this->permissions->pluck('name')->toArray();
    }
}