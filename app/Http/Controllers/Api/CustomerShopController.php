<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;

class CustomerShopController extends Controller
{
    // Get shop details (public)
    public function show($id)
    {
        $shop = Shop::with(['vendor', 'deliveryZones'])
            ->where('id', $id)
            ->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        // Get shop products
        $products = Product::with(['images', 'category'])
            ->where('shop_id', $shop->id)
            ->where('status', 'active')
            ->get()
            ->map(function ($product) {
                $primaryImage = $product->images()->where('is_primary', true)->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'image' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'shop_logo' => $shop->shop_logo ? asset('storage/' . $shop->shop_logo) : null,
                'description' => $shop->description,
                'business_category' => $shop->category,
                'currency' => $shop->currency,
                'primary_contact' => $shop->primary_contact,
                'alternate_contact' => $shop->alternate_contact,
                'business_email' => $shop->business_email,
                'business_address' => $shop->business_address,
                'opening_time' => $shop->opening_time,
                'closing_time' => $shop->closing_time,
                'delivery_zones' => $shop->deliveryZones->map(function ($zone) {
                    return [
                        'location_name' => $zone->location_name,
                        'delivery_fee' => $zone->delivery_fee,
                        'estimated_delivery_time' => $zone->estimated_delivery_time,
                        'delivery_type' => $zone->delivery_type,
                    ];
                }),
                'products' => $products,
            ],
        ]);
    }

    // Get delivery fee estimate for a shop
    public function getDeliveryFee($id)
    {
        $shop = Shop::with('deliveryZones')
            ->where('id', $id)
            ->first();

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        // Get delivery fee from first delivery zone (matching backend order creation logic)
        $deliveryZone = $shop->deliveryZones()->first();
        $deliveryFee = $deliveryZone ? $deliveryZone->delivery_fee : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'shop_id' => $shop->id,
                'delivery_fee' => $deliveryFee,
                'delivery_zone' => $deliveryZone ? [
                    'location_name' => $deliveryZone->location_name,
                    'estimated_delivery_time' => $deliveryZone->estimated_delivery_time,
                    'delivery_type' => $deliveryZone->delivery_type,
                ] : null,
            ],
        ]);
    }
}

