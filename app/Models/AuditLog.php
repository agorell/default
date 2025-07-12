<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'related_type',
        'related_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionTextAttribute()
    {
        $actions = [
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            'view' => 'Viewed',
            'export' => 'Exported',
            'import' => 'Imported',
        ];

        return $actions[$this->action] ?? ucfirst($this->action);
    }

    public function getRelatedTypeTextAttribute()
    {
        $types = [
            'user' => 'User',
            'housing_unit' => 'Housing Unit',
            'occupier' => 'Occupier',
            'note' => 'Note',
            'role' => 'Role',
            'permission' => 'Permission',
        ];

        return $types[$this->related_type] ?? ucfirst(str_replace('_', ' ', $this->related_type));
    }
}