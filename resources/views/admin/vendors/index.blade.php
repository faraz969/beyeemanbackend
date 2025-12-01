<x-admin-layout header="Vendor Management">
    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.vendors.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Name, Email, Phone, Shop Name..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Vendor Status</label>
                    <select name="status" 
                            id="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                
                <div>
                    <label for="shop_status" class="block text-sm font-medium text-gray-700 mb-2">Shop Status</label>
                    <select name="shop_status" 
                            id="shop_status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Shop Status</option>
                        <option value="setup" {{ ($filters['shop_status'] ?? '') === 'setup' ? 'selected' : '' }}>Setup</option>
                        <option value="active" {{ ($filters['shop_status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($filters['shop_status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
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

        <!-- Vendors Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($vendors as $vendor)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $vendor->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $vendor->user->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $vendor->phone }}</div>
                                    <div class="text-sm text-gray-500">{{ $vendor->business_email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($vendor->shop)
                                        <div class="text-sm font-medium text-gray-900">{{ $vendor->shop->shop_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $vendor->shop->category }}</div>
                                    @else
                                        <span class="text-sm text-gray-400">No Shop</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $vendor->status === 'active' ? 'bg-green-100 text-green-800' : 
                                           ($vendor->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($vendor->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($vendor->shop)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $vendor->shop->status === 'active' ? 'bg-green-100 text-green-800' : 
                                               ($vendor->shop->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($vendor->shop->status) }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.vendors.show', $vendor->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No vendors found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($vendors->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $vendors->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

