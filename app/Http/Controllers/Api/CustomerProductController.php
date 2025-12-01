<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CustomerProductController extends Controller
{
    // Get product details
    public function show($id)
    {
        $product = Product::with(['shop', 'category', 'images', 'videos'])
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $images = $product->images->map(function ($image) {
            return asset('storage/' . $image->image_path);
        });

        $videos = $product->videos->map(function ($video) {
            return asset('storage/' . $video->video_path);
        });

        // Calculate delivery fee estimate (simplified - use first delivery zone)
        $deliveryFee = 0;
        $deliveryZone = $product->shop->deliveryZones()->first();
        if ($deliveryZone) {
            $deliveryFee = $deliveryZone->delivery_fee;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'discount' => $product->discount,
                'quantity_available' => $product->quantity_available,
                'sku' => $product->sku,
                'expiry_date' => $product->expiry_date ? $product->expiry_date->format('Y-m-d') : null,
                'weight' => $product->weight,
                'size' => $product->size,
                'delivery_enabled' => $product->delivery_enabled,
                'images' => $images,
                'videos' => $videos,
                'shop' => [
                    'id' => $product->shop->id,
                    'name' => $product->shop->shop_name,
                    'logo' => $product->shop->shop_logo ? asset('storage/' . $product->shop->shop_logo) : null,
                ],
                'category' => [
                    'id' => $product->category->id ?? null,
                    'name' => $product->category->name ?? null,
                ],
                'delivery_fee_estimate' => $deliveryFee,
            ],
        ]);
    }
}

