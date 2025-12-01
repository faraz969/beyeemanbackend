<x-admin-layout header="Customer Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.customers.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Customers
            </a>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center mr-4">
                        <span class="text-white font-bold text-2xl">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $customer->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $customer->email }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                    <p class="text-gray-900">{{ $customer->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Phone</label>
                    <p class="text-gray-900">{{ $customer->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Phone Verified</label>
                    <p class="text-gray-900">
                        @if($customer->phone_verified_at)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Verified on {{ is_string($customer->phone_verified_at) ? \Carbon\Carbon::parse($customer->phone_verified_at)->format('M d, Y') : $customer->phone_verified_at->format('M d, Y') }}
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Not Verified
                            </span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Registered At</label>
                    <p class="text-gray-900">{{ $customer->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Statistics</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Orders</label>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_orders'] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Spent</label>
                    <p class="text-2xl font-semibold text-gray-900">${{ number_format($stats['total_spent'], 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Saved Addresses</label>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_addresses'] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Reviews</label>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_reviews'] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Cart Items</label>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['cart_items'] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Pending Orders</label>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $stats['pending_orders'] }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Completed Orders</label>
                    <p class="text-2xl font-semibold text-green-600">{{ $stats['completed_orders'] }}</p>
                </div>
            </div>
        </div>

        <!-- Saved Addresses -->
        @if($customer->addresses->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Saved Addresses ({{ $customer->addresses->count() }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($customer->addresses as $address)
                        <div class="border border-gray-200 rounded-lg p-4 {{ $address->is_default ? 'bg-blue-50 border-blue-300' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-900">{{ $address->label }}</span>
                                @if($address->is_default)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Default
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-700">{{ $address->street }}</p>
                            @if($address->city)
                                <p class="text-sm text-gray-700">{{ $address->city }}</p>
                            @endif
                            <p class="text-sm text-gray-700">{{ $address->country }}</p>
                            @if($address->latitude && $address->longitude)
                                <p class="text-xs text-gray-500 mt-1">GPS: {{ $address->latitude }}, {{ $address->longitude }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Orders -->
        @if($customer->orders->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order History ({{ $customer->orders->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shop</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($customer->orders->take(10) as $order)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $order->order_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->shop->shop_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->items->count() }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">${{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $order->order_status === 'delivered' ? 'bg-green-100 text-green-800' : 
                                               ($order->order_status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($order->order_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" 
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($customer->orders->count() > 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.orders.index', ['search' => $customer->email]) }}" 
                           class="text-blue-600 hover:text-blue-800">
                            View all {{ $customer->orders->count() }} orders →
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <!-- Cart Items -->
        @if($customer->cart->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cart Items ({{ $customer->cart->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($customer->cart as $cartItem)
                                @php
                                    $primaryImage = $cartItem->product->images->where('is_primary', true)->first() ?? $cartItem->product->images->first();
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            @if($primaryImage)
                                                <img src="{{ asset('storage/' . $primaryImage->image_path) }}" 
                                                     alt="{{ $cartItem->product->name }}" 
                                                     class="h-10 w-10 object-cover rounded-lg mr-3">
                                            @endif
                                            <div class="text-sm font-medium text-gray-900">{{ $cartItem->product->name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $cartItem->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">${{ number_format($cartItem->product->price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Reviews -->
        @if($customer->reviews->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Reviews ({{ $customer->reviews->count() }})</h3>
                <div class="space-y-4">
                    @foreach($customer->reviews as $review)
                        <div class="border-b border-gray-200 pb-4 last:border-0 last:pb-0">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $review->vendor->full_name ?? 'N/A' }}</span>
                                    <div class="flex items-center mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                @if($review->order)
                                    <a href="{{ route('admin.orders.show', $review->order_id) }}" 
                                       class="text-sm text-blue-600 hover:text-blue-800">
                                        Order #{{ $review->order->order_number }}
                                    </a>
                                @endif
                            </div>
                            @if($review->comment)
                                <p class="text-sm text-gray-700 mt-2">{{ $review->comment }}</p>
                            @endif
                            @if($review->image)
                                <img src="{{ asset('storage/' . $review->image) }}" 
                                     alt="Review image" 
                                     class="mt-2 h-24 w-24 object-cover rounded-lg">
                            @endif
                            <p class="text-xs text-gray-500 mt-2">{{ $review->created_at->format('M d, Y') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Availability Requests -->
        @if($customer->availabilityRequests->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Availability Requests ({{ $customer->availabilityRequests->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($customer->availabilityRequests->take(10) as $request)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $request->product->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $request->requested_quantity }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $request->status === 'available' ? 'bg-green-100 text-green-800' : 
                                               ($request->status === 'out_of_stock' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $request->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>

