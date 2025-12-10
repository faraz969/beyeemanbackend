<x-admin-layout header="Product Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.products.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Products
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

        <!-- Product Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Product Information</h3>
                <form method="POST" action="{{ route('admin.products.update-status', $product->id) }}" class="inline-flex items-center space-x-3">
                    @csrf
                    <select name="status" 
                            id="product_status"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="this.form.submit()">
                        <option value="draft" {{ $product->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ $product->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $product->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Product Name</label>
                    <p class="text-gray-900">{{ $product->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">SKU</label>
                    <p class="text-gray-900">{{ $product->sku }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Category</label>
                    <p class="text-gray-900">{{ $product->category->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 
                           ($product->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($product->status) }}
                    </span>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $product->description }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Price</label>
                    <p class="text-gray-900">${{ number_format($product->price, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Discount</label>
                    <p class="text-gray-900">
                        @if($product->discount > 0)
                            {{ $product->discount }}%
                            @php
                                $discountedPrice = $product->price - ($product->price * $product->discount / 100);
                            @endphp
                            <span class="text-green-600 ml-2">(${{ number_format($discountedPrice, 2) }})</span>
                        @else
                            No discount
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Quantity Available</label>
                    <p class="text-gray-900 {{ $product->quantity_available <= 0 ? 'text-red-600 font-semibold' : '' }}">
                        {{ $product->quantity_available }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Delivery Enabled</label>
                    <p class="text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $product->delivery_enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $product->delivery_enabled ? 'Yes' : 'No' }}
                        </span>
                    </p>
                </div>
                @if($product->expiry_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Expiry Date</label>
                        <p class="text-gray-900">{{ $product->expiry_date->format('M d, Y') }}</p>
                    </div>
                @endif
                @if($product->weight)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Weight</label>
                        <p class="text-gray-900">{{ $product->weight }}</p>
                    </div>
                @endif
                @if($product->size)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Size</label>
                        <p class="text-gray-900">{{ $product->size }}</p>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                    <p class="text-gray-900">{{ $product->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Updated At</label>
                    <p class="text-gray-900">{{ $product->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Shop Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Shop Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Shop Name</label>
                    <p class="text-gray-900">{{ $product->shop->shop_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Vendor</label>
                    <p class="text-gray-900">{{ $product->shop->vendor->full_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Shop Category</label>
                    <p class="text-gray-900">{{ $product->shop->category ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Shop Status</label>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $product->shop->status === 'active' ? 'bg-green-100 text-green-800' : 
                           ($product->shop->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($product->shop->status ?? 'N/A') }}
                    </span>
                </div>
                @if($product->shop)
                    <div class="md:col-span-2">
                        <a href="{{ route('admin.vendors.show', $product->shop->vendor_id) }}" 
                           class="text-blue-600 hover:text-blue-800">
                            View Shop Details →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Images -->
        @if($product->images->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Images ({{ $product->images->count() }})</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($product->images as $image)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $image->image_path) }}" 
                                 alt="Product Image" 
                                 class="w-20 h-20 object-cover rounded-lg border border-gray-300">
                            @if($image->is_primary)
                                <span class="absolute top-2 right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">Primary</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Product Video -->
        @if($product->videos->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Video</h3>
                <div class="max-w-2xl">
                    @foreach($product->videos as $video)
                        <video controls class="w-full rounded-lg border border-gray-300">
                            <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Statistics -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">In Cart</label>
                    <p class="text-2xl font-semibold text-gray-900">{{ $product->cartItems->count() }}</p>
                    <p class="text-sm text-gray-500">Items in customer carts</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Availability Requests</label>
                    <p class="text-2xl font-semibold text-gray-900">{{ $product->availabilityRequests->count() }}</p>
                    <p class="text-sm text-gray-500">Customer requests</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Order Items</label>
                    <p class="text-2xl font-semibold text-gray-900">{{ $product->orderItems->count() }}</p>
                    <p class="text-sm text-gray-500">Times ordered</p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

