<x-admin-layout header="Activity Log Details">
    <div class="space-y-6">
        <!-- Activity Log Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Activity Log Details</h2>
                <a href="{{ route('admin.activity-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Back to List
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Action</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $log->action }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $log->description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Type</dt>
                            <dd class="mt-1">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($log->user_type) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($log->user_type === 'admin')
                                    {{ $log->user->name ?? 'System' }}
                                    @if($log->user)
                                        <span class="text-xs text-gray-500">({{ $log->user->email }})</span>
                                    @endif
                                @elseif($log->user_type === 'vendor')
                                    {{ $log->user->full_name ?? 'Unknown Vendor' }}
                                    @if($log->user)
                                        <span class="text-xs text-gray-500">({{ $log->user->phone }})</span>
                                    @endif
                                @elseif($log->user_type === 'customer')
                                    {{ $log->user->name ?? 'Unknown Customer' }}
                                    @if($log->user)
                                        <span class="text-xs text-gray-500">({{ $log->user->email }})</span>
                                    @endif
                                @else
                                    System
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $log->ip_address ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $log->user_agent ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $log->created_at->format('M d, Y H:i:s') }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Model Information</h3>
                    <dl class="space-y-3">
                        @if($log->model_type)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Model Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $log->model_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Model ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $log->model_id }}</dd>
                            </div>
                            @if($log->model)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Model Details</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($log->model_type === 'App\Models\Vendor')
                                            <a href="{{ route('admin.vendors.show', $log->model_id) }}" class="text-blue-600 hover:text-blue-900">
                                                View Vendor
                                            </a>
                                        @elseif($log->model_type === 'App\Models\Product')
                                            <a href="{{ route('admin.products.show', $log->model_id) }}" class="text-blue-600 hover:text-blue-900">
                                                View Product
                                            </a>
                                        @elseif($log->model_type === 'App\Models\Order')
                                            <a href="{{ route('admin.orders.show', $log->model_id) }}" class="text-blue-600 hover:text-blue-900">
                                                View Order
                                            </a>
                                        @else
                                            {{ json_encode($log->model->toArray(), JSON_PRETTY_PRINT) }}
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        @else
                            <div>
                                <span class="text-sm text-gray-400">No model associated with this activity</span>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            @if($log->old_values || $log->new_values)
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Changes</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($log->old_values)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Old Values</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-xs overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                    
                    @if($log->new_values)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">New Values</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-xs overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($log->metadata)
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Metadata</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <pre class="text-xs overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>

