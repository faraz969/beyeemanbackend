<x-admin-layout header="Permission Details">
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

        <!-- Permission Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Permission Information</h3>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Edit
                    </a>
                    @if($permission->roles()->count() == 0)
                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission->id) }}" 
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this permission?');">
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
                    <label class="block text-sm font-medium text-gray-500 mb-1">Permission Name</label>
                    <p class="text-gray-900">{{ $permission->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Guard Name</label>
                    <p class="text-gray-900">{{ $permission->guard_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Roles</label>
                    <p class="text-gray-900">{{ $permission->roles->count() }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Total Users</label>
                    <p class="text-gray-900">{{ $permission->users->count() }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                    <p class="text-gray-900">{{ $permission->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Updated At</label>
                    <p class="text-gray-900">{{ $permission->updated_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Roles with this Permission -->
        @if($permission->roles->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Roles with this Permission ({{ $permission->roles->count() }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permission->roles as $role)
                        <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('admin.roles.show', $role->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    {{ $role->name }}
                                </a>
                                <span class="text-xs text-gray-500">{{ $role->guard_name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500">No roles have this permission yet.</p>
            </div>
        @endif

        <!-- Users with this Permission -->
        @if($permission->users->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Users with this Permission ({{ $permission->users->count() }})</h3>
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
                            @foreach($permission->users->take(10) as $user)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst($user->user_type ?? 'N/A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($permission->users->count() > 10)
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-500">Showing first 10 of {{ $permission->users->count() }} users</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500">No users have this permission directly assigned.</p>
            </div>
        @endif
    </div>
</x-admin-layout>

