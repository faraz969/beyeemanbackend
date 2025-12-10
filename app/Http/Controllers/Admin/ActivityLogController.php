<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'model'])->latest();

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%");
            });
        }

        // Action filter
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // User type filter
        if ($request->has('user_type') && $request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        // Model type filter
        if ($request->has('model_type') && $request->model_type) {
            $query->where('model_type', $request->model_type);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        // Get unique actions, user types, and model types for filters
        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();
        $userTypes = ActivityLog::distinct()->pluck('user_type')->sort()->values();
        $modelTypes = ActivityLog::distinct()->whereNotNull('model_type')->pluck('model_type')->sort()->values();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'userTypes' => $userTypes,
            'modelTypes' => $modelTypes,
            'filters' => $request->only(['search', 'action', 'user_type', 'model_type', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Display the specified activity log.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $log = ActivityLog::with(['user', 'model'])->findOrFail($id);

        return view('admin.activity-logs.show', [
            'log' => $log
        ]);
    }
}
