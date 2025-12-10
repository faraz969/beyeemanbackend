<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Shop;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors and shops.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Vendor::with(['user', 'shop']);
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('business_email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('shop', function($shopQuery) use ($search) {
                      $shopQuery->where('shop_name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Shop status filter
        if ($request->has('shop_status') && $request->shop_status) {
            $query->whereHas('shop', function($shopQuery) use ($request) {
                $shopQuery->where('status', $request->shop_status);
            });
        }
        
        $vendors = $query->latest()->paginate(15);
        
        return view('admin.vendors.index', [
            'vendors' => $vendors,
            'filters' => $request->only(['search', 'status', 'shop_status'])
        ]);
    }

    /**
     * Display the specified vendor.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $vendor = Vendor::with(['user', 'shop.deliveryZones', 'shop.products', 'subscriptions.package', 'wallets'])
            ->findOrFail($id);
        
        return view('admin.vendors.show', [
            'vendor' => $vendor
        ]);
    }

    /**
     * Update vendor status (enable/disable).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,active,suspended'
        ]);
        
        $vendor = Vendor::findOrFail($id);
        $oldStatus = $vendor->status;
        $vendor->status = $request->status;
        $vendor->save();
        
        // Log activity
        ActivityLogService::logStatusChange(
            $vendor,
            'status',
            $oldStatus,
            $request->status,
            "Vendor status changed from '{$oldStatus}' to '{$request->status}'",
            $request
        );
        
        // If suspending vendor, also set shop to inactive
        if ($request->status === 'suspended' && $vendor->shop) {
            $shopOldStatus = $vendor->shop->status;
            $vendor->shop->status = 'inactive';
            $vendor->shop->save();
            
            ActivityLogService::logStatusChange(
                $vendor->shop,
                'status',
                $shopOldStatus,
                'inactive',
                "Shop status changed to 'inactive' due to vendor suspension",
                $request
            );
        }
        
        // If activating vendor, activate shop if it was set up
        if ($request->status === 'active' && $vendor->shop && $vendor->shop->status === 'inactive') {
            $shopOldStatus = $vendor->shop->status;
            $vendor->shop->status = 'active';
            $vendor->shop->save();
            
            ActivityLogService::logStatusChange(
                $vendor->shop,
                'status',
                $shopOldStatus,
                'active',
                "Shop status changed to 'active' due to vendor activation",
                $request
            );
        }
        
        return redirect()->back()->with('success', 'Vendor status updated successfully.');
    }

    /**
     * Update shop status (enable/disable).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $vendorId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateShopStatus(Request $request, $vendorId)
    {
        $request->validate([
            'shop_status' => 'required|in:setup,active,inactive'
        ]);
        
        $vendor = Vendor::with('shop')->findOrFail($vendorId);
        
        if (!$vendor->shop) {
            return redirect()->back()->with('error', 'Vendor does not have a shop.');
        }
        
        $oldStatus = $vendor->shop->status;
        $vendor->shop->status = $request->shop_status;
        $vendor->shop->save();
        
        // Log activity
        ActivityLogService::logStatusChange(
            $vendor->shop,
            'status',
            $oldStatus,
            $request->shop_status,
            "Shop status changed from '{$oldStatus}' to '{$request->shop_status}'",
            $request
        );
        
        return redirect()->back()->with('success', 'Shop status updated successfully.');
    }
}

