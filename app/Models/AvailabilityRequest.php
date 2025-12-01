<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'requested_quantity',
        'status',
        'available_quantity',
        'vendor_notes',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

