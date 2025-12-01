<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'recipient_type',
        'recipient_id',
        'created_by_admin_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }
}
