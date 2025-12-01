<x-admin-layout header="Create Permission">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.permissions.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Permissions
            </a>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Create New Permission</h3>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.permissions.store') }}">
                @csrf

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Permission Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}"
                               required
                               placeholder="e.g., view products, create orders"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-gray-500">Use lowercase letters and spaces (e.g., view products, edit orders)</p>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Guard Name -->
                    <div>
                        <label for="guard_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Guard Name
                        </label>
                        <input type="text" 
                               name="guard_name" 
                               id="guard_name" 
                               value="{{ old('guard_name', 'web') }}"
                               placeholder="web"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-1 text-sm text-gray-500">Default is 'web'. Usually leave as is.</p>
                        @error('guard_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('admin.permissions.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Permission
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

