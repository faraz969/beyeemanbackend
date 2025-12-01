<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'raised_by_user_id',
        'raised_by_type',
        'subject',
        'description',
        'status',
        'resolved_in_favor_of',
        'admin_remarks',
        'resolved_by_admin_id',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function raisedBy()
    {
        return $this->belongsTo(User::class, 'raised_by_user_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_admin_id');
    }
}
