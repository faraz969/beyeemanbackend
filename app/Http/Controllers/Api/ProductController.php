<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVideo;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get categories
    public function categories()
    {
        $categories = Category::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    // Create product
    public function store(Request $request)
    {
        // Convert FormData string values to proper types
        $data = $request->all();
        
        // Convert category_id to integer
        if (isset($data['category_id'])) {
            $data['category_id'] = (int) $data['category_id'];
        }
        
        // Convert price to float
        if (isset($data['price'])) {
            $data['price'] = (float) $data['price'];
        }
        
        // Convert discount to float if provided
        if (isset($data['discount']) && $data['discount'] !== null && $data['discount'] !== '') {
            $data['discount'] = (float) $data['discount'];
        } else {
            $data['discount'] = null;
        }
        
        // Convert quantity_available to integer
        if (isset($data['quantity_available'])) {
            $data['quantity_available'] = (int) $data['quantity_available'];
        }
        
        // Convert delivery_enabled to boolean
        if (isset($data['delivery_enabled'])) {
            // Handle various boolean representations
            $value = $data['delivery_enabled'];
            if (is_string($value)) {
                $value = strtolower($value);
                $data['delivery_enabled'] = in_array($value, ['1', 'true', 'yes', 'on'], true);
            } else {
                $data['delivery_enabled'] = (bool) $value;
            }
        } else {
            $data['delivery_enabled'] = true;
        }

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'quantity_available' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku',
            'batch_no' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'weight' => 'nullable|string',
            'size' => 'nullable|string',
            'delivery_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $vendor = $request->user()->vendor;
        $shop = $vendor->shop;

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found. Please create shop first.',
            ], 404);
        }

        $product = Product::create([
            'shop_id' => $shop->id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'sku' => $data['sku'],
            'batch_no' => $data['batch_no'] ?? null,
            'price' => $data['price'],
            'discount' => $data['discount'] ?? 0,
            'quantity_available' => $data['quantity_available'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'weight' => $data['weight'] ?? null,
            'size' => $data['size'] ?? null,
            'delivery_enabled' => $data['delivery_enabled'],
            'status' => 'draft',
        ]);

        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('product_images', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        // Handle video
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('product_videos', 'public');
            ProductVideo::create([
                'product_id' => $product->id,
                'video_path' => $videoPath,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['images', 'videos', 'category']),
        ], 201);
    }

    // Get vendor's products
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        $shop = $vendor->shop;

        if (!$shop) {
            return response()->json([
                'success' => false,
                'data' => [],
            ]);
        }

        $query = Product::where('shop_id', $shop->id)
            ->with(['images', 'videos', 'category']);

        // Filter by expiry status
        if ($request->has('expiry_filter')) {
            $filter = $request->expiry_filter;
            $today = now()->startOfDay();
            $thirtyDaysFromNow = now()->addDays(30)->endOfDay();

            if ($filter === 'expiring_soon') {
                // Products expiring within 30 days
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '>=', $today)
                    ->where('expiry_date', '<=', $thirtyDaysFromNow);
            } elseif ($filter === 'expired') {
                // Expired products
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', $today);
            } elseif ($filter === 'all') {
                // All products (no filter)
            }
        }

        $products = $query->latest()->get();

        // Add full URLs for images
        $products->transform(function ($product) {
            $productArray = $product->toArray();
            if ($product->images) {
                $productArray['images'] = $product->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_path' => asset('storage/' . $image->image_path),
                        'order' => $image->order,
                        'is_primary' => $image->is_primary,
                    ];
                });
            }
            if ($product->videos) {
                $productArray['videos'] = $product->videos->map(function ($video) {
                    return [
                        'id' => $video->id,
                        'video_path' => asset('storage/' . $video->video_path),
                    ];
                });
            }
            return $productArray;
        });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    // Get single product
    public function show($id)
    {
        $vendor = request()->user()->vendor;
        $shop = $vendor->shop;

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $product = Product::where('shop_id', $shop->id)
            ->where('id', $id)
            ->with(['images', 'videos', 'category'])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Add full URLs for images
        $productData = $product->toArray();
        if ($product->images) {
            $productData['images'] = $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_path' => asset('storage/' . $image->image_path),
                    'order' => $image->order,
                    'is_primary' => $image->is_primary,
                ];
            });
        }
        if ($product->videos) {
            $productData['videos'] = $product->videos->map(function ($video) {
                return [
                    'id' => $video->id,
                    'video_path' => asset('storage/' . $video->video_path),
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => $productData,
        ]);
    }

    // Update product
    public function update(Request $request, $id)
    {
        $vendor = $request->user()->vendor;
        $shop = $vendor->shop;

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $product = Product::where('shop_id', $shop->id)
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Convert FormData string values to proper types
        $data = $request->all();
        
        // Convert category_id to integer if provided
        if (isset($data['category_id'])) {
            $data['category_id'] = (int) $data['category_id'];
        }
        
        // Convert price to float if provided
        if (isset($data['price'])) {
            $data['price'] = (float) $data['price'];
        }
        
        // Convert discount to float if provided
        if (isset($data['discount']) && $data['discount'] !== null && $data['discount'] !== '') {
            $data['discount'] = (float) $data['discount'];
        } else {
            $data['discount'] = null;
        }
        
        // Convert quantity_available to integer if provided
        if (isset($data['quantity_available'])) {
            $data['quantity_available'] = (int) $data['quantity_available'];
        }
        
        // Convert delivery_enabled to boolean if provided
        if (isset($data['delivery_enabled'])) {
            $value = $data['delivery_enabled'];
            if (is_string($value)) {
                $value = strtolower($value);
                $data['delivery_enabled'] = in_array($value, ['1', 'true', 'yes', 'on'], true);
            } else {
                $data['delivery_enabled'] = (bool) $value;
            }
        }

        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'quantity_available' => 'sometimes|required|integer|min:0',
            'sku' => 'sometimes|required|string|unique:products,sku,' . $id,
            'expiry_date' => 'nullable|date',
            'weight' => 'nullable|string',
            'size' => 'nullable|string',
            'delivery_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update product fields
        $updateData = [];
        $fields = ['name', 'category_id', 'description', 'price', 'discount', 'quantity_available', 'sku', 'batch_no', 'expiry_date', 'weight', 'size', 'delivery_enabled'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        $product->update($updateData);

        // Handle new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('product_images', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'order' => $product->images()->count() + $index,
                    'is_primary' => $product->images()->count() === 0 && $index === 0,
                ]);
            }
        }

        // Handle new video
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('product_videos', 'public');
            ProductVideo::create([
                'product_id' => $product->id,
                'video_path' => $videoPath,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load(['images', 'videos', 'category']),
        ]);
    }

    // Delete product
    public function destroy($id)
    {
        $vendor = request()->user()->vendor;
        $shop = $vendor->shop;

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $product = Product::where('shop_id', $shop->id)
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}

