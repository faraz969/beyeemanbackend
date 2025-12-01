<x-admin-layout header="Order Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.orders.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Orders
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $order->order_number }}</h3>
                    <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-900">${{ number_format($order->total_amount, 2) }}</div>
                    <div class="text-sm text-gray-500">Total Amount</div>
                </div>
            </div>

            <!-- Status Update Forms -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="inline-flex items-center space-x-3">
                    @csrf
                    <label class="text-sm font-medium text-gray-700">Order Status:</label>
                    <select name="order_status" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </form>

                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="inline-flex items-center space-x-3">
                    @csrf
                    <label class="text-sm font-medium text-gray-700">Payment Status:</label>
                    <select name="payment_status" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ $order->payment_status === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="full" {{ $order->payment_status === 'full' ? 'selected' : '' }}>Full</option>
                    </select>
                </form>

                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="inline-flex items-center space-x-3">
                    @csrf
                    <label class="text-sm font-medium text-gray-700">Delivery Status:</label>
                    <select name="delivery_status" 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="pending" {{ $order->delivery_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $order->delivery_status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="ready" {{ $order->delivery_status === 'ready' ? 'selected' : '' }}>Ready</option>
                        <option value="out_for_delivery" {{ $order->delivery_status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="delivered" {{ $order->delivery_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                    <p class="text-gray-900">{{ $order->customer->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                    <p class="text-gray-900">{{ $order->customer->email ?? 'N/A' }}</p>
                </div>
                @if($order->customer->phone ?? null)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Phone</label>
                        <p class="text-gray-900">{{ $order->customer->phone }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Shop Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Shop Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Shop Name</label>
                    <p class="text-gray-900">{{ $order->shop->shop_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Vendor</label>
                    <p class="text-gray-900">{{ $order->shop->vendor->full_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Shop Email</label>
                    <p class="text-gray-900">{{ $order->shop->business_email ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Shop Contact</label>
                    <p class="text-gray-900">{{ $order->shop->primary_contact ?? 'N/A' }}</p>
                </div>
                @if($order->shop)
                    <div class="md:col-span-2">
                        <a href="{{ route('admin.vendors.show', $order->shop->vendor_id) }}" 
                           class="text-blue-600 hover:text-blue-800">
                            View Shop Details →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items ({{ $order->items->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            @php
                                $finalPrice = $item->price - ($item->price * ($item->discount ?? 0) / 100);
                                $itemTotal = $finalPrice * $item->quantity;
                                $primaryImage = $item->product->images->where('is_primary', true)->first() ?? $item->product->images->first();
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        @if($primaryImage)
                                            <img src="{{ asset('storage/' . $primaryImage->image_path) }}" 
                                                 alt="{{ $item->product->name }}" 
                                                 class="h-12 w-12 object-cover rounded-lg mr-3">
                                        @else
                                            <div class="h-12 w-12 bg-gray-200 rounded-lg mr-3 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                            @if($item->notes)
                                                <div class="text-xs text-gray-500">Note: {{ $item->notes }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $item->product->category->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    ${{ number_format($item->price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($item->discount > 0)
                                        {{ $item->discount }}%
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    ${{ number_format($itemTotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delivery Address -->
        @if($order->deliveryAddress)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Delivery Address</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Street</label>
                        <p class="text-gray-900">{{ $order->deliveryAddress->street ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">City</label>
                        <p class="text-gray-900">{{ $order->deliveryAddress->city ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Country</label>
                        <p class="text-gray-900">{{ $order->deliveryAddress->country ?? 'N/A' }}</p>
                    </div>
                    @if($order->deliveryAddress->postal_code)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Postal Code</label>
                            <p class="text-gray-900">{{ $order->deliveryAddress->postal_code }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
            <div class="space-y-2">
                @php
                    $subtotal = $order->items->sum(function($item) {
                        $finalPrice = $item->price - ($item->price * ($item->discount ?? 0) / 100);
                        return $finalPrice * $item->quantity;
                    });
                @endphp
                <div class="flex justify-between">
                    <span class="text-gray-700">Subtotal:</span>
                    <span class="text-gray-900 font-medium">${{ number_format($subtotal, 2) }}</span>
                </div>
                @if($order->delivery_fee > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-700">Delivery Fee:</span>
                        <span class="text-gray-900 font-medium">${{ number_format($order->delivery_fee, 2) }}</span>
                    </div>
                @endif
                @if($order->coupon_discount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-700">Coupon ({{ $order->coupon_code }}):</span>
                        <span class="text-green-600 font-medium">-${{ number_format($order->coupon_discount, 2) }}</span>
                    </div>
                @endif
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <div class="flex justify-between">
                        <span class="text-lg font-semibold text-gray-900">Total:</span>
                        <span class="text-lg font-bold text-green-600">${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment & Additional Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment & Additional Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Payment Method</label>
                    <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Availability Confirmed</label>
                    <p class="text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $order->availability_confirmed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $order->availability_confirmed ? 'Yes' : 'No' }}
                        </span>
                    </p>
                </div>
                @if($order->confirmed_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Confirmed At</label>
                        <p class="text-gray-900">{{ $order->confirmed_at->format('M d, Y h:i A') }}</p>
                    </div>
                @endif
                @if($order->customer_notes)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Customer Notes</label>
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $order->customer_notes }}</p>
                    </div>
                @endif
                @if($order->vendor_notes)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Vendor Notes</label>
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $order->vendor_notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>

