<x-admin-layout header="Create Featured Banner">
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

        <!-- Form -->
        <div class="bg-white rounded-lg shadow p-6 max-w-4xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Create New Featured Banner</h3>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Banner Title (Optional)
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="{{ old('title') }}"
                               placeholder="e.g., Summer Sale, New Arrivals"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Image -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                            Banner Image <span class="text-red-500">*</span>
                        </label>
                        <input type="file" 
                               name="image" 
                               id="image" 
                               accept="image/*"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-gray-500">JPEG, PNG, JPG, GIF up to 2MB. Recommended size: 1200x400px</p>
                        @error('image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Image Preview -->
                    <div id="image-preview" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                        <img id="preview-img" src="" alt="Preview" class="h-40 w-full object-cover rounded-lg border border-gray-300">
                    </div>

                    <!-- Link Type -->
                    <div>
                        <label for="link_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Link Type (Optional)
                        </label>
                        <select name="link_type" 
                                id="link_type"
                                onchange="toggleLinkFields()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">No Link</option>
                            <option value="product" {{ old('link_type') === 'product' ? 'selected' : '' }}>Link to Product</option>
                            <option value="vendor" {{ old('link_type') === 'vendor' ? 'selected' : '' }}>Link to Shop/Vendor</option>
                            <option value="category" {{ old('link_type') === 'category' ? 'selected' : '' }}>Link to Category</option>
                            <option value="url" {{ old('link_type') === 'url' ? 'selected' : '' }}>External URL</option>
                        </select>
                        @error('link_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Link ID (for product, vendor, category) -->
                    <div id="link_id_field" style="display: none;">
                        <label for="link_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select <span id="link_id_label"></span>
                        </label>
                        <select name="link_id" 
                                id="link_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- Select --</option>
                        </select>
                        @error('link_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- External URL (for url type) -->
                    <div id="external_url_field" style="display: none;">
                        <label for="external_url" class="block text-sm font-medium text-gray-700 mb-2">
                            External URL
                        </label>
                        <input type="url" 
                               name="external_url" 
                               id="external_url" 
                               value="{{ old('external_url') }}"
                               placeholder="https://example.com"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('external_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                            Display Order
                        </label>
                        <input type="number" 
                               name="order" 
                               id="order" 
                               value="{{ old('order', 0) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first. If not specified, will be added at the end.</p>
                        @error('order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Active banners will be displayed on the customer home screen</p>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('admin.banners.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Banner
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Data for dropdowns
        const products = @json($products);
        const shops = @json($shops);
        const categories = @json($categories);

        function toggleLinkFields() {
            const linkType = document.getElementById('link_type').value;
            const linkIdField = document.getElementById('link_id_field');
            const linkIdSelect = document.getElementById('link_id');
            const linkIdLabel = document.getElementById('link_id_label');
            const externalUrlField = document.getElementById('external_url_field');
            
            // Hide both fields first
            linkIdField.style.display = 'none';
            externalUrlField.style.display = 'none';
            linkIdSelect.innerHTML = '<option value="">-- Select --</option>';
            
            if (linkType === 'product') {
                linkIdField.style.display = 'block';
                linkIdLabel.textContent = 'Product';
                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = product.name + (product.shop ? ' - ' + product.shop.shop_name : '');
                    linkIdSelect.appendChild(option);
                });
            } else if (linkType === 'vendor') {
                linkIdField.style.display = 'block';
                linkIdLabel.textContent = 'Shop';
                shops.forEach(shop => {
                    const option = document.createElement('option');
                    option.value = shop.id;
                    option.textContent = shop.shop_name + (shop.vendor ? ' - ' + shop.vendor.full_name : '');
                    linkIdSelect.appendChild(option);
                });
            } else if (linkType === 'category') {
                linkIdField.style.display = 'block';
                linkIdLabel.textContent = 'Category';
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    linkIdSelect.appendChild(option);
                });
            } else if (linkType === 'url') {
                externalUrlField.style.display = 'block';
            }
        }

        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            } else {
                document.getElementById('image-preview').classList.add('hidden');
            }
        });

        // Initialize on page load if old value exists
        @if(old('link_type'))
            toggleLinkFields();
            @if(old('link_id'))
                setTimeout(() => {
                    document.getElementById('link_id').value = {{ old('link_id') }};
                }, 100);
            @endif
        @endif
    </script>
</x-admin-layout>

