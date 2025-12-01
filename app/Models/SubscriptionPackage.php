<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_type',
        'duration_value',
        'max_products',
        'price',
        'features',
        'featured_listing',
        'featured_listing_count',
        'priority_visibility',
        'free_promotions',
        'dashboard_analytics',
        'is_active',
    ];

    public function subscriptions()
    {
        return $this->hasMany(VendorSubscription::class);
    }
}

