<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'shop_name',
        'shop_logo',
        'description',
        'category',
        'currency',
        'exchange_rate',
        'primary_contact',
        'alternate_contact',
        'business_email',
        'business_address',
        'latitude',
        'longitude',
        'country',
        'street',
        'opening_time',
        'closing_time',
        'status',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

