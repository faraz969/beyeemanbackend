<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'subscription_package_id',
        'payment_method',
        'payment_status',
        'status',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class, 'subscription_package_id');
    }
}

