<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'location_name',
        'delivery_fee',
        'estimated_delivery_time',
        'delivery_type',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}

