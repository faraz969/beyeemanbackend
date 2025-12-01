<x-admin-layout header="Subscription Package Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.subscription-packages.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Packages
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Package Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $package->name }}</h3>
                    <p class="text-sm text-gray-500">Created on {{ $package->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                        {{ $package->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $package->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <a href="{{ route('admin.subscription-packages.edit', $package->id) }}" 
                       class="px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Edit Package
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Price</label>
                    <p class="text-gray-900 text-lg font-semibold">${{ number_format($package->price, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                    <p class="text-gray-900">{{ $package->duration_value }} {{ $package->duration_type }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Max Products</label>
                    <p class="text-gray-900">{{ $package->max_products ?? 'Unlimited' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Featured Listing Count</label>
                    <p class="text-gray-900">{{ $package->featured_listing_count }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Active Subscriptions</label>
                    <p class="text-gray-900 font-semibold">{{ $package->subscriptions_count }}</p>
                </div>
            </div>

            <!-- Features -->
            @if($package->features)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Features</label>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-900 whitespace-pre-wrap">{{ $package->features }}</p>
                    </div>
                </div>
            @endif

            <!-- Package Features -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-500 mb-2">Package Features</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        @if($package->featured_listing)
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        <span class="text-gray-900">Featured Listing</span>
                    </div>
                    <div class="flex items-center">
                        @if($package->priority_visibility)
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        <span class="text-gray-900">Priority Visibility</span>
                    </div>
                    <div class="flex items-center">
                        @if($package->free_promotions)
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        <span class="text-gray-900">Free Promotions</span>
                    </div>
                    <div class="flex items-center">
                        @if($package->dashboard_analytics)
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        <span class="text-gray-900">Dashboard Analytics</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Subscriptions -->
        @if($package->subscriptions && $package->subscriptions->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Active Subscriptions ({{ $package->subscriptions->count() }})</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Starts At</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($package->subscriptions as $subscription)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $subscription->vendor->full_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $subscription->expires_at ? $subscription->expires_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-admin-layout>

