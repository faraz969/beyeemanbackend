<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    /**
     * Display a listing of disputes.
     */
    public function index(Request $request)
    {
        $query = Dispute::with(['order', 'order.shop', 'order.customer', 'raisedBy', 'resolvedBy']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('order', function($q) use ($search) {
                      $q->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Raised by type filter
        if ($request->has('raised_by_type') && $request->raised_by_type) {
            $query->where('raised_by_type', $request->raised_by_type);
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.disputes.index', [
            'disputes' => $disputes,
            'filters' => $request->only(['search', 'status', 'raised_by_type'])
        ]);
    }

    /**
     * Display the specified dispute.
     */
    public function show($id)
    {
        $dispute = Dispute::with([
            'order',
            'order.shop',
            'order.shop.vendor',
            'order.customer',
            'order.items',
            'order.items.product',
            'raisedBy',
            'resolvedBy'
        ])->findOrFail($id);

        return view('admin.disputes.show', [
            'dispute' => $dispute
        ]);
    }

    /**
     * Update dispute status and resolution
     */
    public function update(Request $request, $id)
    {
        $dispute = Dispute::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,under_review,resolved,closed',
            'resolved_in_favor_of' => 'nullable|in:customer,vendor',
            'admin_remarks' => 'nullable|string',
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        // If resolving, set resolved fields
        if ($validated['status'] === 'resolved') {
            $updateData['resolved_in_favor_of'] = $validated['resolved_in_favor_of'] ?? null;
            $updateData['admin_remarks'] = $validated['admin_remarks'] ?? null;
            $updateData['resolved_by_admin_id'] = auth()->id();
            $updateData['resolved_at'] = now();
        } else {
            // If changing from resolved to another status, clear resolution fields
            if ($dispute->status === 'resolved') {
                $updateData['resolved_in_favor_of'] = null;
                $updateData['admin_remarks'] = null;
                $updateData['resolved_by_admin_id'] = null;
                $updateData['resolved_at'] = null;
            }
        }

        $dispute->update($updateData);

        return redirect()->route('admin.disputes.show', $dispute->id)
            ->with('success', 'Dispute updated successfully.');
    }
}
