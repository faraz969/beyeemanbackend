<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'shop_id',
        'order_number',
        'total_amount',
        'subtotal',
        'delivery_fee',
        'processing_fee',
        'platform_fee',
        'payment_status',
        'order_status',
        'delivery_address_id',
        'payment_method',
        'paystack_reference',
        'authorization_code',
        'delivery_status',
        'customer_notes',
        'vendor_notes',
        'coupon_code',
        'coupon_discount',
        'availability_confirmed',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'delivery_address_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }
}

