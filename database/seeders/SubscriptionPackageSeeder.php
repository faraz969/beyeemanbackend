<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPackage;

class SubscriptionPackageSeeder extends Seeder
{
    public function run()
    {
        $packages = [
            [
                'name' => 'Basic Package',
                'duration_type' => 'month',
                'duration_value' => 1,
                'max_products' => 50,
                'price' => 50.00,
                'features' => 'Max 50 products, Limited visibility',
                'featured_listing' => false,
                'featured_listing_count' => 0,
                'priority_visibility' => false,
                'free_promotions' => false,
                'dashboard_analytics' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Standard Package',
                'duration_type' => 'months',
                'duration_value' => 3,
                'max_products' => null, // unlimited
                'price' => 120.00,
                'features' => 'Unlimited products, Full analytics, Featured listing (3 times/month)',
                'featured_listing' => true,
                'featured_listing_count' => 3,
                'priority_visibility' => false,
                'free_promotions' => false,
                'dashboard_analytics' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Premium Package',
                'duration_type' => 'year',
                'duration_value' => 1,
                'max_products' => null, // unlimited
                'price' => 400.00,
                'features' => 'Unlimited products, Priority visibility, Free promotions, Dashboard analytics',
                'featured_listing' => true,
                'featured_listing_count' => 12,
                'priority_visibility' => true,
                'free_promotions' => true,
                'dashboard_analytics' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Market Day (1 day)',
                'duration_type' => 'days',
                'duration_value' => 1,
                'max_products' => null,
                'price' => 10.00,
                'features' => '1 day access, Unlimited products',
                'featured_listing' => false,
                'featured_listing_count' => 0,
                'priority_visibility' => false,
                'free_promotions' => false,
                'dashboard_analytics' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Market Day (2 days)',
                'duration_type' => 'days',
                'duration_value' => 2,
                'max_products' => null,
                'price' => 18.00,
                'features' => '2 days access, Unlimited products',
                'featured_listing' => false,
                'featured_listing_count' => 0,
                'priority_visibility' => false,
                'free_promotions' => false,
                'dashboard_analytics' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Market Day (5 days)',
                'duration_type' => 'days',
                'duration_value' => 5,
                'max_products' => null,
                'price' => 40.00,
                'features' => '5 days access, Unlimited products',
                'featured_listing' => false,
                'featured_listing_count' => 0,
                'priority_visibility' => false,
                'free_promotions' => false,
                'dashboard_analytics' => false,
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            SubscriptionPackage::create($package);
        }
    }
}

