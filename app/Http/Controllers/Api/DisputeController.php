<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DisputeController extends Controller
{
    /**
     * Get disputes for the authenticated user (customer or vendor)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userType = $user->user_type;

        $query = Dispute::with(['order', 'order.shop', 'order.customer', 'resolvedBy'])
            ->where('raised_by_user_id', $user->id);

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $disputes,
        ]);
    }

    /**
     * Create a new dispute
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $userType = $user->user_type;

        // Only customers and vendors can create disputes
        if (!in_array($userType, ['customer', 'vendor'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify that the order belongs to the user
        $order = Order::findOrFail($request->order_id);
        
        if ($userType === 'customer') {
            if ($order->customer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create disputes for your own orders',
                ], 403);
            }
        } elseif ($userType === 'vendor') {
            if ($order->shop->vendor_id !== $user->vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create disputes for orders from your shop',
                ], 403);
            }
        }

        // Check if dispute already exists for this order by this user
        $existingDispute = Dispute::where('order_id', $request->order_id)
            ->where('raised_by_user_id', $user->id)
            ->first();

        if ($existingDispute) {
            return response()->json([
                'success' => false,
                'message' => 'A dispute already exists for this order',
            ], 400);
        }

        $dispute = Dispute::create([
            'order_id' => $request->order_id,
            'raised_by_user_id' => $user->id,
            'raised_by_type' => $userType,
            'subject' => $request->subject,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        $dispute->load(['order', 'order.shop', 'order.customer']);

        return response()->json([
            'success' => true,
            'message' => 'Dispute created successfully',
            'data' => $dispute,
        ], 201);
    }

    /**
     * Get a specific dispute
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $dispute = Dispute::with(['order', 'order.shop', 'order.customer', 'order.items', 'resolvedBy'])
            ->where('raised_by_user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $dispute,
        ]);
    }
}
