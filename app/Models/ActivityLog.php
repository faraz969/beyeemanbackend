<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'user_type',
        'user_id',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the user that performed the action
     */
    public function user()
    {
        if ($this->user_type === 'admin') {
            return $this->belongsTo(\App\Models\User::class, 'user_id');
        } elseif ($this->user_type === 'vendor') {
            return $this->belongsTo(\App\Models\Vendor::class, 'user_id');
        } elseif ($this->user_type === 'customer') {
            return $this->belongsTo(\App\Models\User::class, 'user_id');
        }
        return null;
    }

    /**
     * Get the model that was affected
     */
    public function model()
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }
}
