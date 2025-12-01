<x-admin-layout header="Notification Details">
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

        <!-- Notification Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $notification->title }}</h3>
                    <p class="text-sm text-gray-500">Sent on {{ $notification->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                <div>
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                        @if($notification->type === 'success') bg-green-100 text-green-800
                        @elseif($notification->type === 'warning') bg-yellow-100 text-yellow-800
                        @elseif($notification->type === 'error') bg-red-100 text-red-800
                        @else bg-blue-100 text-blue-800
                        @endif">
                        {{ ucfirst($notification->type) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Recipient Type</label>
                    <p class="text-gray-900 capitalize">{{ $notification->recipient_type }}</p>
                </div>
                @if($notification->recipient)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Recipient</label>
                        <p class="text-gray-900">{{ $notification->recipient->name ?? $notification->recipient->phone }}</p>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Created By</label>
                    <p class="text-gray-900">{{ $notification->createdBy->name ?? 'Admin' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Read Status</label>
                    <p class="text-gray-900">{{ $notification->is_read ? 'Read' : 'Unread' }}</p>
                    @if($notification->read_at)
                        <p class="text-sm text-gray-500">Read at: {{ $notification->read_at->format('M d, Y h:i A') }}</p>
                    @endif
                </div>
            </div>

            <!-- Message -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-500 mb-2">Message</label>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $notification->message }}</p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

