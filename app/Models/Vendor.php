<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'business_email',
        'phone',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function wallets()
    {
        return $this->hasMany(VendorWallet::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

