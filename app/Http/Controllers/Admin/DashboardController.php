<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get statistics
        $stats = [
            'total_shops' => Shop::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_users' => User::where('user_type', 'customer')
                ->whereDoesntHave('roles', function($query) {
                    $query->whereIn('name', ['super-admin', 'admin']);
                })
                ->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
