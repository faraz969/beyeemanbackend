<x-admin-layout header="Order Management">
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Order #, Customer, Shop..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="order_status" class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                    <select name="order_status" 
                            id="order_status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="pending" {{ ($filters['order_status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ ($filters['order_status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ ($filters['order_status'] ?? '') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ ($filters['order_status'] ?? '') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ ($filters['order_status'] ?? '') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ ($filters['order_status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select name="payment_status" 
                            id="payment_status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Payment</option>
                        <option value="pending" {{ ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="full" {{ ($filters['payment_status'] ?? '') === 'full' ? 'selected' : '' }}>Full</option>
                    </select>
                </div>
                
                <div>
                    <label for="delivery_status" class="block text-sm font-medium text-gray-700 mb-2">Delivery Status</label>
                    <select name="delivery_status" 
                            id="delivery_status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Delivery</option>
                        <option value="pending" {{ ($filters['delivery_status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ ($filters['delivery_status'] ?? '') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="ready" {{ ($filters['delivery_status'] ?? '') === 'ready' ? 'selected' : '' }}>Ready</option>
                        <option value="out_for_delivery" {{ ($filters['delivery_status'] ?? '') === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="delivered" {{ ($filters['delivery_status'] ?? '') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>
                
                <div>
                    <label for="shop_id" class="block text-sm font-medium text-gray-700 mb-2">Shop</label>
                    <select name="shop_id" 
                            id="shop_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ ($filters['shop_id'] ?? '') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->shop_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Filter
                    </button>
                </div>
            </form>
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

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow">
            <div style="overflow-x: auto; overflow-y: visible; max-width: 100%;">
                <table class="w-full divide-y divide-gray-200" style="min-width: 1400px; table-layout: auto;">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop/Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $order->customer->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->customer->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $order->shop->shop_name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->shop->vendor->full_name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $order->items->count() }} item(s)</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${{ number_format($order->total_amount, 2) }}</div>
                                    @if($order->delivery_fee > 0)
                                        <div class="text-xs text-gray-500">+${{ number_format($order->delivery_fee, 2) }} delivery</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $order->payment_status === 'full' ? 'bg-green-100 text-green-800' : 
                                           ($order->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $order->order_status === 'delivered' ? 'bg-green-100 text-green-800' : 
                                           ($order->order_status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                           ($order->order_status === 'confirmed' || $order->order_status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $order->delivery_status === 'delivered' ? 'bg-green-100 text-green-800' : 
                                           ($order->delivery_status === 'out_for_delivery' ? 'bg-blue-100 text-blue-800' : 
                                           ($order->delivery_status === 'ready' || $order->delivery_status === 'processing' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" 
                                       class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-4 font-medium rounded-md text-black bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                                    No orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

