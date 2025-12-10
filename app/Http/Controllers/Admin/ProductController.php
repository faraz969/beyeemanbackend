<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Product::with(['shop.vendor', 'category', 'images']);
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('shop', function($shopQuery) use ($search) {
                      $shopQuery->where('shop_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('category', function($catQuery) use ($search) {
                      $catQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Shop filter
        if ($request->has('shop_id') && $request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }
        
        // Category filter
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        $products = $query->latest()->paginate(15);
        
        // Get shops and categories for filters
        $shops = \App\Models\Shop::with('vendor')->orderBy('shop_name')->get();
        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.products.index', [
            'products' => $products,
            'shops' => $shops,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'shop_id', 'category_id'])
        ]);
    }

    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $product = Product::with([
            'shop.vendor',
            'category',
            'images',
            'videos',
            'cartItems',
            'availabilityRequests',
            'orderItems'
        ])->findOrFail($id);
        
        return view('admin.products.show', [
            'product' => $product
        ]);
    }

    /**
     * Update product status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,active,inactive'
        ]);
        
        $product = Product::findOrFail($id);
        $oldStatus = $product->status;
        $product->status = $request->status;
        $product->save();
        
        // Log activity
        ActivityLogService::logStatusChange(
            $product,
            'status',
            $oldStatus,
            $request->status,
            "Product '{$product->name}' status changed from '{$oldStatus}' to '{$request->status}'",
            $request
        );
        
        return redirect()->back()->with('success', 'Product status updated successfully.');
    }
}

