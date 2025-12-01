<x-admin-layout header="Vendor Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.vendors.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Vendors
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

        <!-- Vendor Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Vendor Information</h3>
                <form method="POST" action="{{ route('admin.vendors.update-status', $vendor->id) }}" class="inline-flex items-center space-x-3">
                    @csrf
                    <select name="status" 
                            id="vendor_status"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="pending" {{ $vendor->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ $vendor->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ $vendor->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Full Name</label>
                    <p class="text-gray-900">{{ $vendor->full_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                    <p class="text-gray-900">{{ $vendor->user->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Phone</label>
                    <p class="text-gray-900">{{ $vendor->phone }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Business Email</label>
                    <p class="text-gray-900">{{ $vendor->business_email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $vendor->status === 'active' ? 'bg-green-100 text-green-800' : 
                           ($vendor->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($vendor->status) }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Registered At</label>
                    <p class="text-gray-900">{{ $vendor->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Shop Information -->
        @if($vendor->shop)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Shop Information</h3>
                    <form method="POST" action="{{ route('admin.vendors.update-shop-status', $vendor->id) }}" class="inline-flex items-center space-x-3">
                        @csrf
                        <select name="shop_status" 
                                id="shop_status"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                onchange="this.form.submit()">
                            <option value="setup" {{ $vendor->shop->status === 'setup' ? 'selected' : '' }}>Setup</option>
                            <option value="active" {{ $vendor->shop->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $vendor->shop->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Shop Name</label>
                        <p class="text-gray-900">{{ $vendor->shop->shop_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Category</label>
                        <p class="text-gray-900">{{ $vendor->shop->category }}</p>
                    </div>
                    @if($vendor->shop->shop_logo)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Shop Logo</label>
                            <img src="{{ asset('storage/' . $vendor->shop->shop_logo) }}" 
                                 alt="Shop Logo" 
                                 class="h-24 w-24 object-cover rounded-lg border border-gray-300">
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                        <p class="text-gray-900">{{ $vendor->shop->description ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $vendor->shop->status === 'active' ? 'bg-green-100 text-green-800' : 
                               ($vendor->shop->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($vendor->shop->status) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Primary Contact</label>
                        <p class="text-gray-900">{{ $vendor->shop->primary_contact }}</p>
                    </div>
                    @if($vendor->shop->alternate_contact)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Alternate Contact</label>
                            <p class="text-gray-900">{{ $vendor->shop->alternate_contact }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Business Email</label>
                        <p class="text-gray-900">{{ $vendor->shop->business_email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Business Address</label>
                        <p class="text-gray-900">{{ $vendor->shop->business_address ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Location</label>
                        <p class="text-gray-900">{{ $vendor->shop->street }}, {{ $vendor->shop->country }}</p>
                        @if($vendor->shop->latitude && $vendor->shop->longitude)
                            <p class="text-sm text-gray-500">GPS: {{ $vendor->shop->latitude }}, {{ $vendor->shop->longitude }}</p>
                        @endif
                    </div>
                    @if($vendor->shop->opening_time && $vendor->shop->closing_time)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Opening Hours</label>
                            <p class="text-gray-900">{{ date('h:i A', strtotime($vendor->shop->opening_time)) }} - {{ date('h:i A', strtotime($vendor->shop->closing_time)) }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Total Products</label>
                        <p class="text-gray-900">{{ $vendor->shop->products->count() ?? 0 }}</p>
                    </div>
                </div>

                <!-- Delivery Zones -->
                @if($vendor->shop->deliveryZones->count() > 0)
                    <div class="mt-6">
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Delivery Zones</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery Fee</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($vendor->shop->deliveryZones as $zone)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $zone->location_name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">${{ number_format($zone->delivery_fee, 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $zone->estimated_delivery_time }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst($zone->delivery_type ?? 'standard') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500">This vendor has not set up a shop yet.</p>
            </div>
        @endif

        <!-- Subscription Information -->
        @if($vendor->subscriptions->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Information</h3>
                @foreach($vendor->subscriptions as $subscription)
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-0 last:mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Package</label>
                                <p class="text-gray-900">{{ $subscription->package->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </div>
                            @if($subscription->starts_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Starts At</label>
                                    <p class="text-gray-900">{{ $subscription->starts_at->format('M d, Y') }}</p>
                                </div>
                            @endif
                            @if($subscription->expires_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Expires At</label>
                                    <p class="text-gray-900">{{ $subscription->expires_at->format('M d, Y') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Payment Details -->
        @if($vendor->wallets->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h3>
                @foreach($vendor->wallets as $wallet)
                    <div class="border-b border-gray-200 pb-4 mb-4 last:border-0 last:mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Payment Type</label>
                                <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $wallet->payment_type)) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Provider</label>
                                <p class="text-gray-900">{{ $wallet->provider ?? 'N/A' }}</p>
                            </div>
                            @if($wallet->payment_type === 'mobile_money' && $wallet->momo_number)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 mb-1">Mobile Money Number</label>
                                    <p class="text-gray-900">{{ $wallet->momo_number }}</p>
                                </div>
                            @endif
                            @if($wallet->payment_type === 'bank_account')
                                @if($wallet->account_name)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Account Name</label>
                                        <p class="text-gray-900">{{ $wallet->account_name }}</p>
                                    </div>
                                @endif
                                @if($wallet->bank_name)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Bank Name</label>
                                        <p class="text-gray-900">{{ $wallet->bank_name }}</p>
                                    </div>
                                @endif
                                @if($wallet->account_number)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500 mb-1">Account Number</label>
                                        <p class="text-gray-900">{{ $wallet->account_number }}</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-admin-layout>

