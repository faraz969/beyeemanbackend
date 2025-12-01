<x-admin-layout header="Role Details">
    <div class="space-y-6">
        <!-- Back Button -->
        <div>
            <a href="{{ route('admin.roles.index') }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Roles
            </a>
        </div>

        <!-- Role Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Role Information</h3>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.roles.edit', $role->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Edit
                    </a>
                    @if(!in_array($role->name, ['super-admin', 'admin']))
                        <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" 
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this role?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-red-700 hover:bg-red-50">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Role Name</label>
                    <p class="text-gray-900">{{ $role->name }}</p>
                    @if(in_array($role->name, ['super-admin', 'admin']))
                        <span class="inline-block mt-1 px-2 text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Protected Role
                        </span>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Guard Name</label>
                    <p class="text-gray-900">{{ $role->guard_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Permissions</label>
                    <p class="text-gray-900">{{ $role->permissions->count() }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Users</label>
                    <p class="text-gray-900">{{ $role->users->count() }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                    <p class="text-gray-900">{{ $role->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Updated At</label>
                    <p class="text-gray-900">{{ $role->updated_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        @if($role->permissions->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Permissions ({{ $role->permissions->count() }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($role->permissions as $permission)
                        <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm text-gray-900">{{ $permission->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500">This role has no permissions assigned.</p>
            </div>
        @endif

        <!-- Users with this Role -->
        @if($role->users->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Users with this Role ({{ $role->users->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User Type</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($role->users->take(10) as $user)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst($user->user_type ?? 'N/A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($role->users->count() > 10)
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-500">Showing first 10 of {{ $role->users->count() }} users</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500">No users have this role yet.</p>
            </div>
        @endif
    </div>
</x-admin-layout>

