<x-admin-layout header="Create Subscription Package">
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
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Create Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Create New Subscription Package</h3>
            
            <form method="POST" action="{{ route('admin.subscription-packages.store') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Package Name *</label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price ($) *</label>
                        <input type="number" 
                               name="price" 
                               id="price"
                               step="0.01"
                               min="0"
                               value="{{ old('price') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Duration Type -->
                    <div>
                        <label for="duration_type" class="block text-sm font-medium text-gray-700 mb-2">Duration Type *</label>
                        <select name="duration_type" 
                                id="duration_type"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Duration Type</option>
                            <option value="days" {{ old('duration_type') === 'days' ? 'selected' : '' }}>Days</option>
                            <option value="month" {{ old('duration_type') === 'month' ? 'selected' : '' }}>Month</option>
                            <option value="months" {{ old('duration_type') === 'months' ? 'selected' : '' }}>Months</option>
                            <option value="year" {{ old('duration_type') === 'year' ? 'selected' : '' }}>Year</option>
                        </select>
                    </div>

                    <!-- Duration Value -->
                    <div>
                        <label for="duration_value" class="block text-sm font-medium text-gray-700 mb-2">Duration Value *</label>
                        <input type="number" 
                               name="duration_value" 
                               id="duration_value"
                               min="1"
                               value="{{ old('duration_value') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Max Products -->
                    <div>
                        <label for="max_products" class="block text-sm font-medium text-gray-700 mb-2">Max Products (leave empty for unlimited)</label>
                        <input type="number" 
                               name="max_products" 
                               id="max_products"
                               min="0"
                               value="{{ old('max_products') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Featured Listing Count -->
                    <div>
                        <label for="featured_listing_count" class="block text-sm font-medium text-gray-700 mb-2">Featured Listing Count</label>
                        <input type="number" 
                               name="featured_listing_count" 
                               id="featured_listing_count"
                               min="0"
                               value="{{ old('featured_listing_count', 0) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-6">
                    <label for="features" class="block text-sm font-medium text-gray-700 mb-2">Features (one per line)</label>
                    <textarea name="features" 
                              id="features"
                              rows="5"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('features') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Enter features separated by new lines</p>
                </div>

                <!-- Checkboxes -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="featured_listing" 
                               id="featured_listing"
                               value="1"
                               {{ old('featured_listing') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="featured_listing" class="ml-2 block text-sm text-gray-900">Featured Listing</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="priority_visibility" 
                               id="priority_visibility"
                               value="1"
                               {{ old('priority_visibility') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="priority_visibility" class="ml-2 block text-sm text-gray-900">Priority Visibility</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="free_promotions" 
                               id="free_promotions"
                               value="1"
                               {{ old('free_promotions') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="free_promotions" class="ml-2 block text-sm text-gray-900">Free Promotions</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="dashboard_analytics" 
                               id="dashboard_analytics"
                               value="1"
                               {{ old('dashboard_analytics') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="dashboard_analytics" class="ml-2 block text-sm text-gray-900">Dashboard Analytics</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" 
                            class="px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

