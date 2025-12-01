<x-admin-layout header="Notifications Management">
    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Notifications</h2>
            <a href="{{ route('admin.notifications.create') }}" 
               class="px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Send New Notification
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Title, message..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="recipient_type" class="block text-sm font-medium text-gray-700 mb-2">Recipient Type</label>
                    <select name="recipient_type" 
                            id="recipient_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="all" {{ ($filters['recipient_type'] ?? '') === 'all' ? 'selected' : '' }}>All Users</option>
                        <option value="vendor" {{ ($filters['recipient_type'] ?? '') === 'vendor' ? 'selected' : '' }}>Vendors</option>
                        <option value="customer" {{ ($filters['recipient_type'] ?? '') === 'customer' ? 'selected' : '' }}>Customers</option>
                        <option value="specific" {{ ($filters['recipient_type'] ?? '') === 'specific' ? 'selected' : '' }}>Specific User</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Filter
                    </button>
                </div>
            </form>
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

        <!-- Notifications Table -->
        <div class="bg-white rounded-lg shadow">
            <div style="overflow-x: auto; overflow-y: visible; max-width: 100%;">
                <table class="w-full divide-y divide-gray-200" style="min-width: 1000px; table-layout: auto;">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($notifications as $notification)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ Str::limit($notification->title, 50) }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($notification->message, 60) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($notification->type === 'success') bg-green-100 text-green-800
                                        @elseif($notification->type === 'warning') bg-yellow-100 text-yellow-800
                                        @elseif($notification->type === 'error') bg-red-100 text-red-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ ucfirst($notification->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 capitalize">{{ $notification->recipient_type }}</div>
                                    @if($notification->recipient)
                                        <div class="text-xs text-gray-500">{{ $notification->recipient->name ?? $notification->recipient->phone }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $notification->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.notifications.show', $notification->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 mr-4">View</a>
                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this notification?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No notifications found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

