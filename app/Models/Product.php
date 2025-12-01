<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'category_id',
        'name',
        'description',
        'sku',
        'batch_no',
        'price',
        'discount',
        'quantity_available',
        'expiry_date',
        'weight',
        'size',
        'delivery_enabled',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function videos()
    {
        return $this->hasMany(ProductVideo::class);
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function availabilityRequests()
    {
        return $this->hasMany(AvailabilityRequest::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}

