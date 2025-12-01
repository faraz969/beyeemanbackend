<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get vendor's orders
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $shop = $vendor->shop;
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found. Please create shop first.',
            ], 404);
        }

        $status = $request->input('status'); // Filter by order_status
        $deliveryStatus = $request->input('delivery_status'); // Filter by delivery_status

        $orders = Order::with(['customer', 'items.product.images', 'deliveryAddress'])
            ->where('shop_id', $shop->id)
            ->when($status, function ($query) use ($status) {
                return $query->where('order_status', $status);
            })
            ->when($deliveryStatus, function ($query) use ($deliveryStatus) {
                return $query->where('delivery_status', $deliveryStatus);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Format order items with full image URLs
                $order->items->transform(function ($item) {
                    if ($item->product && $item->product->images) {
                        $item->product->images->transform(function ($image) {
                            $image->image_path = asset('storage/' . $image->image_path);
                            return $image;
                        });
                    }
                    return $item;
                });
                return $order;
            });

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    // Get single order
    public function show($id, Request $request)
    {
        $vendor = $request->user()->vendor;
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $shop = $vendor->shop;
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $order = Order::with(['customer', 'items.product.images', 'deliveryAddress'])
            ->where('id', $id)
            ->where('shop_id', $shop->id)
            ->firstOrFail();

        // Format order items with full image URLs
        $order->items->transform(function ($item) {
            if ($item->product && $item->product->images) {
                $item->product->images->transform(function ($image) {
                    $image->image_path = asset('storage/' . $image->image_path);
                    return $image;
                });
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    // Update order status
    public function update($id, Request $request)
    {
        $vendor = $request->user()->vendor;
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $shop = $vendor->shop;
        
        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop not found',
            ], 404);
        }

        $order = Order::where('id', $id)
            ->where('shop_id', $shop->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'order_status' => 'sometimes|required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'delivery_status' => 'sometimes|required|in:pending,processing,ready,out_for_delivery,delivered',
            'vendor_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update order status
        if ($request->has('order_status')) {
            $order->order_status = $request->order_status;
            
            // If order is cancelled, update delivery status too
            if ($request->order_status === 'cancelled') {
                $order->delivery_status = 'pending';
            }
            
            // If order is delivered, update both statuses
            if ($request->order_status === 'delivered') {
                $order->delivery_status = 'delivered';
            }
        }
        
        if ($request->has('delivery_status')) {
            $order->delivery_status = $request->delivery_status;
            
            // If delivery is delivered, update order status too
            if ($request->delivery_status === 'delivered') {
                $order->order_status = 'delivered';
            }
        }
        
        if ($request->has('vendor_notes')) {
            $order->vendor_notes = $request->vendor_notes;
        }
        
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data' => $order->load('customer', 'items.product', 'deliveryAddress'),
        ]);
    }
}

