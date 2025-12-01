<x-admin-layout header="Category Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.categories.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Categories
            </a>
        </div>

        <!-- Category Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Category Information</h3>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.categories.edit', $category->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" 
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this category?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-red-700 hover:bg-red-50">
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($category->image)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Image</label>
                        <img src="{{ asset('storage/' . $category->image) }}" 
                             alt="{{ $category->name }}" 
                             class="h-48 w-48 object-cover rounded-lg border border-gray-300">
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Category Name</label>
                    <p class="text-gray-900">{{ $category->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Slug</label>
                    <p class="text-gray-900">{{ $category->slug }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $category->description ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Products</label>
                    <p class="text-gray-900">{{ $category->products_count }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                    <p class="text-gray-900">{{ $category->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Updated At</label>
                    <p class="text-gray-900">{{ $category->updated_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Products in this Category -->
        @if($category->products->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Products in this Category ({{ $category->products_count }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shop</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($category->products as $product)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $product->shop->shop_name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">${{ number_format($product->price, 2) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($product->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium">
                                        <a href="{{ route('admin.products.show', $product->id) }}" 
                                           class="text-blue-600 hover:text-blue-900">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($category->products_count > 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" 
                           class="text-blue-600 hover:text-blue-800">
                            View all {{ $category->products_count }} products →
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500">No products in this category yet.</p>
            </div>
        @endif
    </div>
</x-admin-layout>

