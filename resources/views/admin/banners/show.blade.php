<x-admin-layout header="Banner Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.banners.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Banners
            </a>
        </div>

        <!-- Banner Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Banner Information</h3>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.banners.edit', $banner->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.banners.destroy', $banner->id) }}" 
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this banner?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-red-700 hover:bg-red-50">
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Banner Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">Banner Image</label>
                    <img src="{{ asset('storage/' . $banner->image) }}" 
                         alt="{{ $banner->title ?? 'Banner' }}" 
                         class="h-64 w-full object-cover rounded-lg border border-gray-300">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Title</label>
                        <p class="text-gray-900">{{ $banner->title ?? 'No Title' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $banner->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $banner->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Display Order</label>
                        <p class="text-gray-900">{{ $banner->order }}</p>
                    </div>
                    @if($banner->link_type)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Link Type</label>
                            <p class="text-gray-900 capitalize">{{ $banner->link_type }}</p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                        <p class="text-gray-900">{{ $banner->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Updated At</label>
                        <p class="text-gray-900">{{ $banner->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>

                <!-- Link Information -->
                @if($banner->link_type)
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-md font-semibold text-gray-900 mb-4">Link Information</h4>
                        
                        @if($banner->link_type === 'url' && $banner->external_url)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">External URL</label>
                                <a href="{{ $banner->external_url }}" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800 break-all">
                                    {{ $banner->external_url }}
                                </a>
                            </div>
                        @elseif($linkedItem)
                            <div class="space-y-4">
                                @if($banner->link_type === 'product')
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h5 class="font-semibold text-gray-900 mb-2">Linked Product</h5>
                                        <p class="text-sm text-gray-900">Name: {{ $linkedItem->name }}</p>
                                        @if($linkedItem->shop)
                                            <p class="text-sm text-gray-600">Shop: {{ $linkedItem->shop->shop_name }}</p>
                                        @endif
                                        <a href="{{ route('admin.products.show', $linkedItem->id) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                            View Product →
                                        </a>
                                    </div>
                                @elseif($banner->link_type === 'vendor')
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h5 class="font-semibold text-gray-900 mb-2">Linked Shop</h5>
                                        <p class="text-sm text-gray-900">Shop Name: {{ $linkedItem->shop_name }}</p>
                                        @if($linkedItem->vendor)
                                            <p class="text-sm text-gray-600">Vendor: {{ $linkedItem->vendor->full_name }}</p>
                                        @endif
                                        <a href="{{ route('admin.vendors.show', $linkedItem->vendor_id) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                            View Shop →
                                        </a>
                                    </div>
                                @elseif($banner->link_type === 'category')
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h5 class="font-semibold text-gray-900 mb-2">Linked Category</h5>
                                        <p class="text-sm text-gray-900">Name: {{ $linkedItem->name }}</p>
                                        <a href="{{ route('admin.categories.show', $linkedItem->id) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                                            View Category →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif($banner->link_id)
                            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <p class="text-sm text-yellow-800">
                                    Linked {{ ucfirst($banner->link_type) }} with ID {{ $banner->link_id }} not found. 
                                    It may have been deleted.
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="border-t border-gray-200 pt-6">
                        <p class="text-gray-500">This banner has no link configured.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>

