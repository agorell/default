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
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModel($query, $modelType, $modelId = null)
    {
        $query = $query->where('model_type', $modelType);
        
        if ($modelId) {
            $query = $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function getActionLabelAttribute()
    {
        $actions = [
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'restore' => 'Restored',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            'view' => 'Viewed',
            'export' => 'Exported',
            'import' => 'Imported',
        ];
        
        return $actions[$this->action] ?? ucfirst($this->action);
    }

    public function getModelNameAttribute()
    {
        $modelParts = explode('\\', $this->model_type);
        return end($modelParts);
    }

    public function getChangesAttribute()
    {
        $changes = [];
        
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $field => $newValue) {
                if (isset($this->old_values[$field]) && $this->old_values[$field] !== $newValue) {
                    $changes[] = [
                        'field' => $field,
                        'old' => $this->old_values[$field],
                        'new' => $newValue
                    ];
                }
            }
        }
        
        return $changes;
    }

    public function hasChanges()
    {
        return count($this->getChangesAttribute()) > 0;
    }

    public function getFormattedChangesAttribute()
    {
        $changes = $this->getChangesAttribute();
        $formatted = [];
        
        foreach ($changes as $change) {
            $formatted[] = "{$change['field']}: '{$change['old']}' → '{$change['new']}'";
        }
        
        return implode(', ', $formatted);
    }

    public static function logActivity($action, $model, $oldValues = null, $newValues = null, $description = null)
    {
        $user = auth()->user();
        
        if (!$user) {
            return;
        }
        
        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id ?? null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
        ]);
    }

    public static function logLogin($user)
    {
        return self::create([
            'user_id' => $user->id,
            'action' => 'login',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => 'User logged in',
        ]);
    }

    public static function logLogout($user)
    {
        return self::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'model_type' => get_class($user),
            'model_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => 'User logged out',
        ]);
    }
}