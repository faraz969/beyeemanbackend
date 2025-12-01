<x-admin-layout header="Dispute Management">
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.disputes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Subject, description, order number..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" 
                            id="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="under_review" {{ ($filters['status'] ?? '') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="resolved" {{ ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                
                <div>
                    <label for="raised_by_type" class="block text-sm font-medium text-gray-700 mb-2">Raised By</label>
                    <select name="raised_by_type" 
                            id="raised_by_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="customer" {{ ($filters['raised_by_type'] ?? '') === 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="vendor" {{ ($filters['raised_by_type'] ?? '') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
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

        <!-- Disputes Table -->
        <div class="bg-white rounded-lg shadow">
            <div style="overflow-x: auto; overflow-y: visible; max-width: 100%;">
                <table class="w-full divide-y divide-gray-200" style="min-width: 1200px; table-layout: auto;">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raised By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved In Favor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($disputes as $dispute)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $dispute->order->order_number ?? 'N/A' }}</div>
                                    @if($dispute->order)
                                        <div class="text-xs text-gray-500">Shop: {{ $dispute->order->shop->shop_name ?? 'N/A' }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ Str::limit($dispute->subject, 50) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 capitalize">{{ $dispute->raised_by_type }}</div>
                                    @if($dispute->raisedBy)
                                        <div class="text-xs text-gray-500">{{ $dispute->raisedBy->name ?? $dispute->raisedBy->phone }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($dispute->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($dispute->status === 'under_review') bg-blue-100 text-blue-800
                                        @elseif($dispute->status === 'resolved') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($dispute->resolved_in_favor_of)
                                        <span class="text-sm text-gray-900 capitalize">{{ $dispute->resolved_in_favor_of }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $dispute->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.disputes.show', $dispute->id) }}" 
                                       class="text-blue-600 hover:text-blue-900">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No disputes found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($disputes->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $disputes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

