<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    /**
     * Display a listing of transfers.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['order', 'vendor', 'vendorWallet']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transfer_reference', 'like', "%{$search}%")
                  ->orWhere('transfer_code', 'like', "%{$search}%")
                  ->orWhere('recipient_code', 'like', "%{$search}%")
                  ->orWhereHas('order', function($orderQuery) use ($search) {
                      $orderQuery->where('order_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vendor', function($vendorQuery) use ($search) {
                      $vendorQuery->where('full_name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Order by latest first
        $transfers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.transfers.index', compact('transfers'));
    }

    /**
     * Display the specified transfer.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $transfer = Transfer::with(['order.items', 'vendor', 'vendorWallet'])
            ->findOrFail($id);

        return view('admin.transfers.show', compact('transfer'));
    }

    /**
     * Verify transfer status with Paystack
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify($id)
    {
        $transfer = Transfer::findOrFail($id);

        $paystackService = app(PaystackService::class);
        $result = $paystackService->verifyTransfer($transfer->transfer_reference);

        if ($result['success']) {
            $transferData = $result['data'];
            $transfer->update([
                'status' => $transferData['status'] ?? $transfer->status,
                'transfer_code' => $transferData['transfer_code'] ?? $transfer->transfer_code,
                'paystack_response' => $transferData,
                'transferred_at' => ($transferData['status'] ?? '') === 'success' && isset($transferData['transferred_at']) 
                    ? $transferData['transferred_at'] 
                    : $transfer->transferred_at,
                'failure_reason' => ($transferData['status'] ?? '') === 'failed' 
                    ? ($transferData['gateway_response'] ?? 'Transfer failed') 
                    : null,
            ]);

            return redirect()->route('admin.transfers.show', $transfer->id)
                ->with('success', 'Transfer status updated successfully.');
        }

        return redirect()->route('admin.transfers.show', $transfer->id)
            ->with('error', 'Failed to verify transfer: ' . ($result['message'] ?? 'Unknown error'));
    }
}
