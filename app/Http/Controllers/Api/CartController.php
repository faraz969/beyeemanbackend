<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Get cart items
    public function index(Request $request)
    {
        $cartItems = Cart::with(['product.shop', 'product.images', 'product.category'])
            ->where('customer_id', $request->user()->id)
            ->get()
            ->map(function ($item) {
                $primaryImage = $item->product->images()->where('is_primary', true)->first();
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'discount' => $item->product->discount,
                        'image' => $primaryImage ? asset('storage/' . $primaryImage->image_path) : null,
                        'shop_id' => $item->product->shop_id ?? null,
                        'shop_name' => $item->product->shop->shop_name ?? '',
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $cartItems,
        ]);
    }

    // Add to cart
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        // Check if item already in cart
        $cartItem = Cart::where('customer_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->notes = $request->notes ?? $cartItem->notes;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'customer_id' => $request->user()->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => $cartItem,
        ], 201);
    }

    // Update cart item
    public function update($id, Request $request)
    {
        $cartItem = Cart::where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('quantity')) {
            $cartItem->quantity = $request->quantity;
        }
        if ($request->has('notes')) {
            $cartItem->notes = $request->notes;
        }
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => $cartItem,
        ]);
    }

    // Remove from cart
    public function destroy($id, Request $request)
    {
        $cartItem = Cart::where('id', $id)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }
}

