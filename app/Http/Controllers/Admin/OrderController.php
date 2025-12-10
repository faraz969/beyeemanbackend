<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'shop.vendor', 'items']);
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('shop', function($shopQuery) use ($search) {
                      $shopQuery->where('shop_name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Order status filter
        if ($request->has('order_status') && $request->order_status) {
            $query->where('order_status', $request->order_status);
        }
        
        // Payment status filter
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Delivery status filter
        if ($request->has('delivery_status') && $request->delivery_status) {
            $query->where('delivery_status', $request->delivery_status);
        }
        
        // Shop filter
        if ($request->has('shop_id') && $request->shop_id) {
            $query->where('shop_id', $request->shop_id);
        }
        
        $orders = $query->latest()->paginate(15);
        
        // Get shops for filter
        $shops = \App\Models\Shop::with('vendor')->orderBy('shop_name')->get();
        
        return view('admin.orders.index', [
            'orders' => $orders,
            'shops' => $shops,
            'filters' => $request->only(['search', 'order_status', 'payment_status', 'delivery_status', 'shop_id'])
        ]);
    }

    /**
     * Display the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $order = Order::with([
            'customer',
            'shop.vendor',
            'deliveryAddress',
            'items.product.images',
            'items.product.category'
        ])->findOrFail($id);
        
        return view('admin.orders.show', [
            'order' => $order
        ]);
    }

    /**
     * Update order status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'sometimes|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|in:pending,partial,full',
            'delivery_status' => 'sometimes|in:pending,processing,ready,out_for_delivery,delivered'
        ]);
        
        $order = Order::findOrFail($id);
        $oldValues = [
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'delivery_status' => $order->delivery_status,
        ];
        
        if ($request->has('order_status')) {
            $order->order_status = $request->order_status;
        }
        
        if ($request->has('payment_status')) {
            $order->payment_status = $request->payment_status;
        }
        
        if ($request->has('delivery_status')) {
            $order->delivery_status = $request->delivery_status;
        }
        
        $order->save();
        
        // Log activity
        $newValues = [
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'delivery_status' => $order->delivery_status,
        ];
        
        $changes = [];
        if ($oldValues['order_status'] !== $newValues['order_status']) {
            $changes[] = "Order status: {$oldValues['order_status']} → {$newValues['order_status']}";
        }
        if ($oldValues['payment_status'] !== $newValues['payment_status']) {
            $changes[] = "Payment status: {$oldValues['payment_status']} → {$newValues['payment_status']}";
        }
        if ($oldValues['delivery_status'] !== $newValues['delivery_status']) {
            $changes[] = "Delivery status: {$oldValues['delivery_status']} → {$newValues['delivery_status']}";
        }
        
        if (!empty($changes)) {
            ActivityLogService::logUpdate(
                $order,
                "Order #{$order->order_number} status updated: " . implode(', ', $changes),
                $oldValues,
                $newValues,
                $request
            );
        }
        
        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}

