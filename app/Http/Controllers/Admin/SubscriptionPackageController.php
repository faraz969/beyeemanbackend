<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;

class SubscriptionPackageController extends Controller
{
    /**
     * Display a listing of subscription packages.
     */
    public function index(Request $request)
    {
        $query = SubscriptionPackage::query();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        $packages = $query->withCount('subscriptions')->orderBy('price', 'asc')->paginate(15);

        return view('admin.subscription-packages.index', [
            'packages' => $packages,
            'filters' => $request->only(['search', 'is_active'])
        ]);
    }

    /**
     * Show the form for creating a new subscription package.
     */
    public function create()
    {
        return view('admin.subscription-packages.create');
    }

    /**
     * Store a newly created subscription package.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_packages,name',
            'duration_type' => 'required|in:month,months,year,days',
            'duration_value' => 'required|integer|min:1',
            'max_products' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|string',
            'featured_listing' => 'boolean',
            'featured_listing_count' => 'nullable|integer|min:0',
            'priority_visibility' => 'boolean',
            'free_promotions' => 'boolean',
            'dashboard_analytics' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['featured_listing'] = $request->has('featured_listing');
        $validated['priority_visibility'] = $request->has('priority_visibility');
        $validated['free_promotions'] = $request->has('free_promotions');
        $validated['dashboard_analytics'] = $request->has('dashboard_analytics');
        $validated['is_active'] = $request->has('is_active');

        SubscriptionPackage::create($validated);

        return redirect()->route('admin.subscription-packages.index')
            ->with('success', 'Subscription package created successfully.');
    }

    /**
     * Display the specified subscription package.
     */
    public function show($id)
    {
        $package = SubscriptionPackage::withCount('subscriptions')
            ->with(['subscriptions.vendor', 'subscriptions.vendor.user'])
            ->findOrFail($id);

        return view('admin.subscription-packages.show', [
            'package' => $package
        ]);
    }

    /**
     * Show the form for editing the specified subscription package.
     */
    public function edit($id)
    {
        $package = SubscriptionPackage::findOrFail($id);

        return view('admin.subscription-packages.edit', [
            'package' => $package
        ]);
    }

    /**
     * Update the specified subscription package.
     */
    public function update(Request $request, $id)
    {
        $package = SubscriptionPackage::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_packages,name,' . $id,
            'duration_type' => 'required|in:month,months,year,days',
            'duration_value' => 'required|integer|min:1',
            'max_products' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|string',
            'featured_listing' => 'boolean',
            'featured_listing_count' => 'nullable|integer|min:0',
            'priority_visibility' => 'boolean',
            'free_promotions' => 'boolean',
            'dashboard_analytics' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['featured_listing'] = $request->has('featured_listing');
        $validated['priority_visibility'] = $request->has('priority_visibility');
        $validated['free_promotions'] = $request->has('free_promotions');
        $validated['dashboard_analytics'] = $request->has('dashboard_analytics');
        $validated['is_active'] = $request->has('is_active');

        $package->update($validated);

        return redirect()->route('admin.subscription-packages.show', $package->id)
            ->with('success', 'Subscription package updated successfully.');
    }

    /**
     * Remove the specified subscription package.
     */
    public function destroy($id)
    {
        $package = SubscriptionPackage::findOrFail($id);

        // Prevent deletion if package has active subscriptions
        if ($package->subscriptions()->where('status', 'active')->exists()) {
            return redirect()->route('admin.subscription-packages.index')
                ->with('error', 'Cannot delete package with active subscriptions.');
        }

        $package->delete();

        return redirect()->route('admin.subscription-packages.index')
            ->with('success', 'Subscription package deleted successfully.');
    }
}
