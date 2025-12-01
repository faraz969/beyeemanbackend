<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Create review
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'order_id' => 'nullable|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify order belongs to customer (if provided)
        if ($request->order_id) {
            $order = Order::where('id', $request->order_id)
                ->where('customer_id', $request->user()->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('reviews', 'public');
        }

        $review = Review::create([
            'customer_id' => $request->user()->id,
            'vendor_id' => $request->vendor_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review,
        ], 201);
    }

    // Get vendor reviews
    public function vendorReviews($vendorId)
    {
        $reviews = Review::with('customer')
            ->where('vendor_id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'image' => $review->image ? asset('storage/' . $review->image) : null,
                    'customer_name' => $review->customer->name ?? 'Anonymous',
                    'created_at' => $review->created_at,
                ];
            });

        $averageRating = Review::where('vendor_id', $vendorId)->avg('rating');

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'average_rating' => round($averageRating, 2),
                'total_reviews' => $reviews->count(),
            ],
        ]);
    }
}

