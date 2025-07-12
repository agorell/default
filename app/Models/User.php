<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        'phone',
        'address',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->role && $this->role->name === $role;
        }
        
        if (is_array($role)) {
            return $this->role && in_array($this->role->name, $role);
        }
        
        return false;
    }

    public function hasPermission($permission)
    {
        if (!$this->role) {
            return false;
        }
        
        return $this->role->permissions()->where('name', $permission)->exists();
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}