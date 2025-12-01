<x-admin-layout header="Fee Settings">
    <div class="space-y-6">
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

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Configure processing and platform fees that will be charged at order placement. You can set fees as either a percentage or fixed amount.
                    </p>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Fee Configuration</h3>
            
            <form method="POST" action="{{ route('admin.fee-settings.update') }}">
                @csrf
                @method('PUT')

                <div class="space-y-8">
                    <!-- Processing Fee Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h4 class="text-md font-semibold text-gray-900 mb-4">Processing Fee</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Processing Fee Type -->
                            <div>
                                <label for="processing_fee_type" class="block text-sm font-medium text-gray-700 mb-2">Fee Type *</label>
                                <select name="processing_fee_type" 
                                        id="processing_fee_type"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="percentage" {{ old('processing_fee_type', $settings->processing_fee_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('processing_fee_type', $settings->processing_fee_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                                </select>
                            </div>

                            <!-- Processing Fee Value -->
                            <div>
                                <label for="processing_fee_value" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fee Value *
                                    <span id="processing_fee_label" class="text-gray-500 text-xs">
                                        {{ old('processing_fee_type', $settings->processing_fee_type) === 'percentage' ? '(0-100%)' : '($)' }}
                                    </span>
                                </label>
                                <input type="number" 
                                       name="processing_fee_value" 
                                       id="processing_fee_value"
                                       step="0.01"
                                       min="0"
                                       max="{{ old('processing_fee_type', $settings->processing_fee_type) === 'percentage' ? '100' : '' }}"
                                       value="{{ old('processing_fee_value', $settings->processing_fee_value) }}"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Processing Fee Applicable To -->
                            <div>
                                <label for="processing_fee_applicable_to" class="block text-sm font-medium text-gray-700 mb-2">Charged To *</label>
                                <select name="processing_fee_applicable_to" 
                                        id="processing_fee_applicable_to"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="customer" {{ old('processing_fee_applicable_to', $settings->processing_fee_applicable_to) === 'customer' ? 'selected' : '' }}>Customer</option>
                                    <option value="vendor" {{ old('processing_fee_applicable_to', $settings->processing_fee_applicable_to) === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                    <option value="both" {{ old('processing_fee_applicable_to', $settings->processing_fee_applicable_to) === 'both' ? 'selected' : '' }}>Both (Split)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Fee Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h4 class="text-md font-semibold text-gray-900 mb-4">Platform Fee</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Platform Fee Type -->
                            <div>
                                <label for="platform_fee_type" class="block text-sm font-medium text-gray-700 mb-2">Fee Type *</label>
                                <select name="platform_fee_type" 
                                        id="platform_fee_type"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="percentage" {{ old('platform_fee_type', $settings->platform_fee_type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('platform_fee_type', $settings->platform_fee_type) === 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                                </select>
                            </div>

                            <!-- Platform Fee Value -->
                            <div>
                                <label for="platform_fee_value" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fee Value *
                                    <span id="platform_fee_label" class="text-gray-500 text-xs">
                                        {{ old('platform_fee_type', $settings->platform_fee_type) === 'percentage' ? '(0-100%)' : '($)' }}
                                    </span>
                                </label>
                                <input type="number" 
                                       name="platform_fee_value" 
                                       id="platform_fee_value"
                                       step="0.01"
                                       min="0"
                                       max="{{ old('platform_fee_type', $settings->platform_fee_type) === 'percentage' ? '100' : '' }}"
                                       value="{{ old('platform_fee_value', $settings->platform_fee_value) }}"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <!-- Platform Fee Applicable To -->
                            <div>
                                <label for="platform_fee_applicable_to" class="block text-sm font-medium text-gray-700 mb-2">Charged To *</label>
                                <select name="platform_fee_applicable_to" 
                                        id="platform_fee_applicable_to"
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="customer" {{ old('platform_fee_applicable_to', $settings->platform_fee_applicable_to) === 'customer' ? 'selected' : '' }}>Customer</option>
                                    <option value="vendor" {{ old('platform_fee_applicable_to', $settings->platform_fee_applicable_to) === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                    <option value="both" {{ old('platform_fee_applicable_to', $settings->platform_fee_applicable_to) === 'both' ? 'selected' : '' }}>Both (Split)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" 
                            class="px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Fee Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript to update labels and max values when type changes -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Processing Fee Type Change
            const processingFeeType = document.getElementById('processing_fee_type');
            const processingFeeValue = document.getElementById('processing_fee_value');
            const processingFeeLabel = document.getElementById('processing_fee_label');
            
            processingFeeType.addEventListener('change', function() {
                if (this.value === 'percentage') {
                    processingFeeLabel.textContent = '(0-100%)';
                    processingFeeValue.setAttribute('max', '100');
                } else {
                    processingFeeLabel.textContent = '($)';
                    processingFeeValue.removeAttribute('max');
                }
            });

            // Platform Fee Type Change
            const platformFeeType = document.getElementById('platform_fee_type');
            const platformFeeValue = document.getElementById('platform_fee_value');
            const platformFeeLabel = document.getElementById('platform_fee_label');
            
            platformFeeType.addEventListener('change', function() {
                if (this.value === 'percentage') {
                    platformFeeLabel.textContent = '(0-100%)';
                    platformFeeValue.setAttribute('max', '100');
                } else {
                    platformFeeLabel.textContent = '($)';
                    platformFeeValue.removeAttribute('max');
                }
            });
        });
    </script>
</x-admin-layout>

