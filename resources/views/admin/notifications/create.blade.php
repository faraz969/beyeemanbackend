<x-admin-layout header="Send Notification">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.notifications.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Notifications
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
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Send New Notification</h3>
            
            <form method="POST" action="{{ route('admin.notifications.store') }}">
                @csrf

                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" 
                               name="title" 
                               id="title"
                               value="{{ old('title') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Notification title">
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                        <textarea name="message" 
                                  id="message"
                                  rows="5"
                                  required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Notification message">{{ old('message') }}</textarea>
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                        <select name="type" 
                                id="type"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Success</option>
                            <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="error" {{ old('type') === 'error' ? 'selected' : '' }}>Error</option>
                        </select>
                    </div>

                    <!-- Recipient Type -->
                    <div>
                        <label for="recipient_type" class="block text-sm font-medium text-gray-700 mb-2">Recipient Type *</label>
                        <select name="recipient_type" 
                                id="recipient_type"
                                required
                                onchange="toggleRecipientField()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="all" {{ old('recipient_type') === 'all' ? 'selected' : '' }}>All Users (Vendors & Customers)</option>
                            <option value="vendor" {{ old('recipient_type') === 'vendor' ? 'selected' : '' }}>All Vendors</option>
                            <option value="customer" {{ old('recipient_type') === 'customer' ? 'selected' : '' }}>All Customers</option>
                            <option value="specific" {{ old('recipient_type') === 'specific' ? 'selected' : '' }}>Specific User</option>
                        </select>
                    </div>

                    <!-- Specific Recipient (shown only when specific is selected) -->
                    <div id="recipient_id_field" style="display: {{ old('recipient_type') === 'specific' ? 'block' : 'none' }};">
                        <label for="recipient_id" class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <input type="text" 
                               name="recipient_id" 
                               id="recipient_id"
                               value="{{ old('recipient_id') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter user ID or search...">
                        <p class="mt-1 text-sm text-gray-500">Enter the user ID of the specific user to notify</p>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" 
                                class="px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Send Notification
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleRecipientField() {
            const recipientType = document.getElementById('recipient_type').value;
            const recipientIdField = document.getElementById('recipient_id_field');
            
            if (recipientType === 'specific') {
                recipientIdField.style.display = 'block';
                document.getElementById('recipient_id').required = true;
            } else {
                recipientIdField.style.display = 'none';
                document.getElementById('recipient_id').required = false;
            }
        }
    </script>
</x-admin-layout>

