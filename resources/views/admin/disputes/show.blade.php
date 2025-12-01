<x-admin-layout header="Dispute Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.disputes.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Disputes
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Dispute Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $dispute->subject }}</h3>
                    <p class="text-sm text-gray-500">Created on {{ $dispute->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                <div>
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                        @if($dispute->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($dispute->status === 'under_review') bg-blue-100 text-blue-800
                        @elseif($dispute->status === 'resolved') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Order Number</label>
                    <p class="text-gray-900">{{ $dispute->order->order_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Raised By</label>
                    <p class="text-gray-900 capitalize">{{ $dispute->raised_by_type }}</p>
                    @if($dispute->raisedBy)
                        <p class="text-sm text-gray-600">{{ $dispute->raisedBy->name ?? $dispute->raisedBy->phone }}</p>
                    @endif
                </div>
                @if($dispute->order)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Customer</label>
                        <p class="text-gray-900">{{ $dispute->order->customer->name ?? $dispute->order->customer->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Shop</label>
                        <p class="text-gray-900">{{ $dispute->order->shop->shop_name ?? 'N/A' }}</p>
                    </div>
                @endif
                @if($dispute->resolved_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Resolved At</label>
                        <p class="text-gray-900">{{ $dispute->resolved_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    @if($dispute->resolvedBy)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Resolved By</label>
                            <p class="text-gray-900">{{ $dispute->resolvedBy->name }}</p>
                        </div>
                    @endif
                @endif
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $dispute->description }}</p>
                </div>
            </div>

            @if($dispute->admin_remarks)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Admin Remarks</label>
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $dispute->admin_remarks }}</p>
                    </div>
                </div>
            @endif

            @if($dispute->resolved_in_favor_of)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Resolved In Favor Of</label>
                    <p class="text-gray-900 capitalize font-semibold">{{ $dispute->resolved_in_favor_of }}</p>
                </div>
            @endif
        </div>

        <!-- Order Details -->
        @if($dispute->order)
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Order Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Order Number</label>
                        <p class="text-gray-900">{{ $dispute->order->order_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Total Amount</label>
                        <p class="text-gray-900">${{ number_format($dispute->order->total_amount, 2) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Order Status</label>
                        <p class="text-gray-900 capitalize">{{ str_replace('_', ' ', $dispute->order->order_status) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Payment Status</label>
                        <p class="text-gray-900 capitalize">{{ str_replace('_', ' ', $dispute->order->payment_status) }}</p>
                    </div>
                </div>

                @if($dispute->order->items && $dispute->order->items->count() > 0)
                    <div class="mt-6">
                        <h5 class="text-sm font-semibold text-gray-900 mb-3">Order Items</h5>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($dispute->order->items as $item)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->quantity }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">${{ number_format($item->price, 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">${{ number_format($item->quantity * $item->price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Update Dispute Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-md font-semibold text-gray-900 mb-4">Update Dispute</h4>
            <form method="POST" action="{{ route('admin.disputes.update', $dispute->id) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" 
                                id="status"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="pending" {{ $dispute->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="under_review" {{ $dispute->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="resolved" {{ $dispute->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $dispute->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div id="resolution_fields" style="display: {{ $dispute->status === 'resolved' ? 'block' : 'none' }};">
                        <div class="mb-4">
                            <label for="resolved_in_favor_of" class="block text-sm font-medium text-gray-700 mb-2">Resolved In Favor Of (Optional)</label>
                            <select name="resolved_in_favor_of" 
                                    id="resolved_in_favor_of"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Not Specified</option>
                                <option value="customer" {{ $dispute->resolved_in_favor_of === 'customer' ? 'selected' : '' }}>Customer</option>
                                <option value="vendor" {{ $dispute->resolved_in_favor_of === 'vendor' ? 'selected' : '' }}>Vendor</option>
                            </select>
                        </div>

                        <div>
                            <label for="admin_remarks" class="block text-sm font-medium text-gray-700 mb-2">Admin Remarks (Optional)</label>
                            <textarea name="admin_remarks" 
                                      id="admin_remarks"
                                      rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('admin_remarks', $dispute->admin_remarks) }}</textarea>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                                class="px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Dispute
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('status').addEventListener('change', function() {
            const resolutionFields = document.getElementById('resolution_fields');
            if (this.value === 'resolved') {
                resolutionFields.style.display = 'block';
            } else {
                resolutionFields.style.display = 'none';
            }
        });
    </script>
</x-admin-layout>

