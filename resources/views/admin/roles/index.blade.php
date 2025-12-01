<x-admin-layout header="Role Management">
    <div class="space-y-6">
        <!-- Header with Create Button -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">Roles</h2>
            <a href="{{ route('admin.roles.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Role
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.roles.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Role name..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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

        <!-- Roles Table -->
        <div class="bg-white rounded-lg shadow">
            <div style="overflow-x: auto; overflow-y: visible; max-width: 100%;">
                <table class="w-full divide-y divide-gray-200" style="min-width: 900px; table-layout: auto;">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guard</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $role->name }}</div>
                                    @if(in_array($role->name, ['super-admin', 'admin']))
                                        <div class="text-xs text-red-600 mt-1">Protected Role</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500">{{ $role->guard_name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900">{{ $role->permissions_count }}</span>
                                    @if($role->permissions_count > 0)
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $role->permissions->take(3)->pluck('name')->implode(', ') }}
                                            @if($role->permissions_count > 3)
                                                <span class="text-gray-400">+{{ $role->permissions_count - 3 }} more</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $role->users_count }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $role->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                           class="text-blue-600 hover:text-blue-900">Edit</a>
                                        @if(!in_array($role->name, ['super-admin', 'admin']))
                                            <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No roles found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($roles->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

