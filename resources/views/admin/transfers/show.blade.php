<x-admin-layout header="Transfer Details">
    <div class="space-y-6">
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

        <!-- Transfer Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Transfer Details</h2>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.transfers.verify', $transfer->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Verify Status
                        </button>
                    </form>
                    <a href="{{ route('admin.transfers.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Back to List
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Transfer Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Transfer Reference</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer->transfer_reference }}</dd>
                        </div>
                        @if($transfer->transfer_code)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Transfer Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer->transfer_code }}</dd>
                        </div>
                        @endif
                        @if($transfer->recipient_code)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Recipient Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transfer->recipient_code }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'queued' => 'bg-blue-100 text-blue-800',
                                        'success' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'reversed' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $color = $statusColors[$transfer->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $color }}">
                                    {{ ucfirst($transfer->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $transfer->currency }} {{ number_format($transfer->amount, 2) }}</dd>
                        </div>
                        @if($transfer->processing_fee > 0)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Processing Fee</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $transfer->currency }} {{ number_format($transfer->processing_fee, 2) }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Reason</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $transfer->reason ?? 'N/A' }}</dd>
                        </div>
                        @if($transfer->failure_reason)
                        <div>
                            <dt class="text-sm font-medium text-red-500">Failure Reason</dt>
                            <dd class="mt-1 text-sm text-red-900">{{ $transfer->failure_reason }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $transfer->created_at->format('M d, Y H:i:s') }}</dd>
                        </div>
                        @if($transfer->transferred_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Transferred At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $transfer->transferred_at->format('M d, Y H:i:s') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Order Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="{{ route('admin.orders.show', $transfer->order_id) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ $transfer->order->order_number ?? 'N/A' }}
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Vendor</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $transfer->vendor->full_name ?? 'N/A' }}</dd>
                        </div>
                        @if($transfer->vendorWallet)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $transfer->vendorWallet->payment_type)) }}</dd>
                        </div>
                        @if($transfer->vendorWallet->payment_type === 'mobile_money')
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Mobile Money Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $transfer->vendorWallet->momo_number ?? 'N/A' }}</dd>
                        </div>
                        @elseif($transfer->vendorWallet->payment_type === 'bank_account')
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Bank Account</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $transfer->vendorWallet->bank_name ?? 'N/A' }} - 
                                {{ $transfer->vendorWallet->account_number ?? 'N/A' }}
                            </dd>
                        </div>
                        @endif
                        @endif
                    </dl>
                </div>
            </div>

            @if($transfer->paystack_response)
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Paystack Response</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <pre class="text-xs overflow-x-auto">{{ json_encode($transfer->paystack_response, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>

