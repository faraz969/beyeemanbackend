<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedBanner;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Display a listing of featured banners.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = FeaturedBanner::query();
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }
        
        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status == 'active');
        }
        
        // Link type filter
        if ($request->has('link_type') && $request->link_type) {
            $query->where('link_type', $request->link_type);
        }
        
        $banners = $query->orderBy('order')->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.banners.index', [
            'banners' => $banners,
            'filters' => $request->only(['search', 'status', 'link_type'])
        ]);
    }

    /**
     * Show the form for creating a new banner.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get lists for dropdowns
        $products = Product::with('shop')->orderBy('name')->limit(100)->get();
        $shops = Shop::with('vendor')->orderBy('shop_name')->limit(100)->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.banners.create', [
            'products' => $products,
            'shops' => $shops,
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created banner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link_type' => 'nullable|in:product,vendor,category,url',
            'link_id' => 'nullable|integer|required_if:link_type,product,vendor,category',
            'external_url' => 'nullable|url|required_if:link_type,url',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('featured_banners', 'public');
        }
        
        // Handle is_active checkbox
        $validated['is_active'] = $request->has('is_active');
        
        // Set order if not provided
        if (!isset($validated['order'])) {
            $validated['order'] = FeaturedBanner::max('order') + 1 ?? 0;
        }
        
        // Clear link_id if link_type is url
        if ($validated['link_type'] === 'url') {
            $validated['link_id'] = null;
        }
        
        // Clear external_url if link_type is not url
        if ($validated['link_type'] !== 'url') {
            $validated['external_url'] = null;
        }
        
        FeaturedBanner::create($validated);
        
        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner created successfully.');
    }

    /**
     * Display the specified banner.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $banner = FeaturedBanner::findOrFail($id);
        
        // Load linked item if applicable
        $linkedItem = null;
        if ($banner->link_type && $banner->link_id) {
            switch ($banner->link_type) {
                case 'product':
                    $linkedItem = Product::with('shop')->find($banner->link_id);
                    break;
                case 'vendor':
                    $linkedItem = Shop::with('vendor')->find($banner->link_id);
                    break;
                case 'category':
                    $linkedItem = Category::find($banner->link_id);
                    break;
            }
        }
        
        return view('admin.banners.show', [
            'banner' => $banner,
            'linkedItem' => $linkedItem
        ]);
    }

    /**
     * Show the form for editing the specified banner.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $banner = FeaturedBanner::findOrFail($id);
        
        // Get lists for dropdowns
        $products = Product::with('shop')->orderBy('name')->limit(100)->get();
        $shops = Shop::with('vendor')->orderBy('shop_name')->limit(100)->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.banners.edit', [
            'banner' => $banner,
            'products' => $products,
            'shops' => $shops,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified banner.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $banner = FeaturedBanner::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'link_type' => 'nullable|in:product,vendor,category,url',
            'link_id' => 'nullable|integer|required_if:link_type,product,vendor,category',
            'external_url' => 'nullable|url|required_if:link_type,url',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('featured_banners', 'public');
        }
        
        // Handle is_active checkbox
        $validated['is_active'] = $request->has('is_active');
        
        // Clear link_id if link_type is url or null
        if ($validated['link_type'] === 'url' || !$validated['link_type']) {
            $validated['link_id'] = null;
        }
        
        // Clear external_url if link_type is not url or null
        if ($validated['link_type'] !== 'url' || !$validated['link_type']) {
            $validated['external_url'] = null;
        }
        
        $banner->update($validated);
        
        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner updated successfully.');
    }

    /**
     * Remove the specified banner.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $banner = FeaturedBanner::findOrFail($id);
        
        // Delete image if exists
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        
        $banner->delete();
        
        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner deleted successfully.');
    }
}

