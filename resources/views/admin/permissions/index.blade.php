<x-admin-layout header="Permission Management">
    <div class="space-y-6">
        <!-- Header with Create Button -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">Permissions</h2>
            <a href="{{ route('admin.permissions.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-black rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Permission
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6">
            <form method="GET" action="{{ route('admin.permissions.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Permission name..."
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

        <!-- Permissions by Group -->
        @foreach($groupedPermissions as $group => $groupPermissions)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 capitalize">{{ $group }} Permissions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permission</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guard</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($groupPermissions as $permission)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $permission->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-500">{{ $permission->guard_name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ $permission->roles_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ $permission->users_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $permission->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.permissions.edit', $permission->id) }}" 
                                               class="text-blue-600 hover:text-blue-900">Edit</a>
                                            @if($permission->roles_count == 0)
                                                <form method="POST" action="{{ route('admin.permissions.destroy', $permission->id) }}" 
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this permission?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        @if($permissions->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-lg shadow">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>

