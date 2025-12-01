<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::where('user_type', 'customer')
            ->whereDoesntHave('roles') // Exclude users with admin roles
            ->with(['addresses', 'orders', 'reviews']);
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Phone verified filter
        if ($request->has('phone_verified') && $request->phone_verified !== '') {
            if ($request->phone_verified == '1') {
                $query->whereNotNull('phone_verified_at');
            } else {
                $query->whereNull('phone_verified_at');
            }
        }
        
        $customers = $query->latest()->paginate(15);
        
        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'phone_verified'])
        ]);
    }

    /**
     * Display the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $customer = User::where('user_type', 'customer')
            ->whereDoesntHave('roles')
            ->with([
                'addresses',
                'orders.shop.vendor',
                'orders.items.product',
                'cart.product.images',
                'availabilityRequests.product',
                'reviews.vendor'
            ])
            ->findOrFail($id);
        
        // Calculate statistics
        $stats = [
            'total_orders' => $customer->orders->count(),
            'total_spent' => $customer->orders->sum('total_amount'),
            'total_addresses' => $customer->addresses->count(),
            'total_reviews' => $customer->reviews->count(),
            'cart_items' => $customer->cart->count(),
            'pending_orders' => $customer->orders->where('order_status', 'pending')->count(),
            'completed_orders' => $customer->orders->where('order_status', 'delivered')->count(),
        ];
        
        return view('admin.customers.show', [
            'customer' => $customer,
            'stats' => $stats
        ]);
    }
}

