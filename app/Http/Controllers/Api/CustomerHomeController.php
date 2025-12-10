<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeaturedBanner;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerHomeController extends Controller
{
    // Get home screen data
    public function index(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Featured Banners
        $banners = FeaturedBanner::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image' => asset('storage/' . $banner->image),
                    'link_type' => $banner->link_type,
                    'link_id' => $banner->link_id,
                    'external_url' => $banner->external_url,
                ];
            });

        // Nearby Shops (if GPS provided)
        // Show all shops regardless of status for now (can filter later)
        $shops = Shop::with('vendor')
            ->when($latitude && $longitude, function ($query) use ($latitude, $longitude) {
                // Calculate distance using Haversine formula
                return $query->select('*')
                    ->selectRaw(
                        '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                        [$latitude, $longitude, $latitude]
                    )
                    ->orderBy('distance');
            }, function ($query) {
                return $query->orderBy('created_at', 'desc');
            })
            ->limit(10)
            ->get()
            ->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->shop_name,
                    'logo' => $shop->shop_logo ? asset('storage/' . $shop->shop_logo) : null,
                    'description' => $shop->description,
                    'category' => $shop->category,
                ];
            });

        // Featured Products
        // Show all products regardless of status for now (can filter later)
        $products = Product::with(['shop', 'category', 'images'])
            ->whereHas('shop')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($product) {
                $primaryImage = $product->images()->where('is_primary', true)->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'image' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                    'shop_name' => $product->shop->shop_name ?? '',
                    'shop_id' => $product->shop->id ?? null,
                    'currency' => $product->shop->currency ?? 'USD',
                    'category' => $product->category->name ?? '',
                ];
            });

        // Categories
        $categories = Category::where('is_active', true)
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'banners' => $banners,
                'shops' => $shops,
                'products' => $products,
                'categories' => $categories,
            ],
        ]);
    }

    // Search products and vendors
    public function search(Request $request)
    {
        $query = $request->input('q');
        $categoryId = $request->input('category_id');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        $products = Product::with(['shop', 'category', 'images'])
            ->where('status', 'active')
            ->whereHas('shop', function ($q) {
                $q->where('status', 'active');
            })
            ->when($query, function ($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                return $q->where('category_id', $categoryId);
            })
            ->when($minPrice, function ($q) use ($minPrice) {
                return $q->where('price', '>=', $minPrice);
            })
            ->when($maxPrice, function ($q) use ($maxPrice) {
                return $q->where('price', '<=', $maxPrice);
            })
            ->get()
            ->map(function ($product) {
                $primaryImage = $product->images()->where('is_primary', true)->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'image' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                    'shop_name' => $product->shop->shop_name ?? '',
                    'shop_id' => $product->shop->id ?? null,
                    'currency' => $product->shop->currency ?? 'USD',
                    'category' => $product->category->name ?? '',
                ];
            });

        $shops = Shop::with('vendor')
            ->where('status', 'active')
            ->when($query, function ($q) use ($query) {
                return $q->where('shop_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->get()
            ->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->shop_name,
                    'logo' => $shop->shop_logo ? asset('storage/' . $shop->shop_logo) : null,
                    'description' => $shop->short_description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'shops' => $shops,
            ],
        ]);
    }

    // Get all categories
    public function categories()
    {
        $categories = Category::where('is_active', true)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'image' => $category->image ? asset('storage/' . $category->image) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    // Get shops by category
    public function shopsByCategory(Request $request, $categoryId)
    {
        $category = Category::find($categoryId);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Get shops where category matches the category name
        $shops = Shop::with('vendor')
            ->where('category', $category->name)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->shop_name,
                    'logo' => $shop->shop_logo ? asset('storage/' . $shop->shop_logo) : null,
                    'description' => $shop->description,
                    'category' => $shop->category,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $shops,
        ]);
    }
}

