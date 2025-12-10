<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    /**
     * Log an activity
     *
     * @param string $action Action identifier (e.g., 'vendor.created', 'product.updated')
     * @param string $description Human-readable description
     * @param Model|null $model The model that was affected
     * @param array|null $oldValues Old values (for updates)
     * @param array|null $newValues New values (for updates)
     * @param array|null $metadata Additional metadata
     * @param Request|null $request Request object to extract IP and user agent
     * @return ActivityLog
     */
    public static function log(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?Request $request = null
    ): ActivityLog {
        $userType = null;
        $userId = null;

        // Determine user type and ID from authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            $userId = $user->id;

            // Check if user is admin
            if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
                $userType = 'admin';
            } elseif ($user->vendor) {
                $userType = 'vendor';
                $userId = $user->vendor->id;
            } else {
                $userType = 'customer';
            }
        } else {
            $userType = 'system';
        }

        // Extract IP and user agent from request
        $ipAddress = $request ? $request->ip() : request()->ip();
        $userAgent = $request ? $request->userAgent() : request()->userAgent();

        return ActivityLog::create([
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'user_type' => $userType,
            'user_id' => $userId,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Log a create action
     */
    public static function logCreate(Model $model, string $description, ?Request $request = null): ActivityLog
    {
        $action = strtolower(class_basename($model)) . '.created';
        return self::log($action, $description, $model, null, $model->toArray(), null, $request);
    }

    /**
     * Log an update action
     */
    public static function logUpdate(
        Model $model,
        string $description,
        array $oldValues,
        array $newValues,
        ?Request $request = null
    ): ActivityLog {
        $action = strtolower(class_basename($model)) . '.updated';
        return self::log($action, $description, $model, $oldValues, $newValues, null, $request);
    }

    /**
     * Log a delete action
     */
    public static function logDelete(Model $model, string $description, ?Request $request = null): ActivityLog
    {
        $action = strtolower(class_basename($model)) . '.deleted';
        return self::log($action, $description, $model, $model->toArray(), null, null, $request);
    }

    /**
     * Log a status change
     */
    public static function logStatusChange(
        Model $model,
        string $field,
        $oldValue,
        $newValue,
        string $description = null,
        ?Request $request = null
    ): ActivityLog {
        $action = strtolower(class_basename($model)) . '.status_changed';
        $description = $description ?? ucfirst($field) . " changed from '{$oldValue}' to '{$newValue}'";
        
        return self::log(
            $action,
            $description,
            $model,
            [$field => $oldValue],
            [$field => $newValue],
            null,
            $request
        );
    }
}

