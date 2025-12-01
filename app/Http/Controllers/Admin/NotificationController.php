<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $query = Notification::with(['createdBy', 'recipient']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Recipient type filter
        if ($request->has('recipient_type') && $request->recipient_type) {
            $query->where('recipient_type', $request->recipient_type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'filters' => $request->only(['search', 'recipient_type'])
        ]);
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        return view('admin.notifications.create');
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,error',
            'recipient_type' => 'required|in:all,vendor,customer,specific',
            'recipient_id' => 'required_if:recipient_type,specific|nullable|exists:users,id',
        ]);

        $adminId = auth()->id();

        DB::beginTransaction();
        try {
            if ($validated['recipient_type'] === 'all') {
                // Send to all users
                $users = User::whereIn('user_type', ['vendor', 'customer'])->get();
                foreach ($users as $user) {
                    Notification::create([
                        'title' => $validated['title'],
                        'message' => $validated['message'],
                        'type' => $validated['type'],
                        'recipient_type' => 'all',
                        'recipient_id' => $user->id,
                        'created_by_admin_id' => $adminId,
                    ]);
                }
            } elseif ($validated['recipient_type'] === 'vendor' || $validated['recipient_type'] === 'customer') {
                // Send to all vendors or customers
                $users = User::where('user_type', $validated['recipient_type'])->get();
                foreach ($users as $user) {
                    Notification::create([
                        'title' => $validated['title'],
                        'message' => $validated['message'],
                        'type' => $validated['type'],
                        'recipient_type' => $validated['recipient_type'],
                        'recipient_id' => $user->id,
                        'created_by_admin_id' => $adminId,
                    ]);
                }
            } else {
                // Send to specific user
                Notification::create([
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'type' => $validated['type'],
                    'recipient_type' => 'specific',
                    'recipient_id' => $validated['recipient_id'],
                    'created_by_admin_id' => $adminId,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.notifications.index')
                ->with('success', 'Notification sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified notification.
     */
    public function show($id)
    {
        $notification = Notification::with(['createdBy', 'recipient'])->findOrFail($id);

        return view('admin.notifications.show', [
            'notification' => $notification
        ]);
    }

    /**
     * Remove the specified notification.
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }
}
