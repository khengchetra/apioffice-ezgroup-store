<?php

namespace App\Services;

use App\Models\User;
use App\Models\CompilePermission;

class PermissionService
{
    /**
     * Check if a user has a specific permission or request category.
     *
     * @param int $userId
     * @param int|null $checkPermissionId
     * @param int|null $reqestCategoryId
     * @return bool
     */
    public function checkPermission(int $userId, ?int $checkPermissionId = null, ?int $reqestCategoryId = null): bool
    {
        // Ensure only one of checkPermissionId or reqestCategoryId is provided
        if ($checkPermissionId !== null && $reqestCategoryId !== null) {
            return false; // Both cannot be provided
        }

        // Get the user's role_id from tbluser
        $user = User::find($userId);

        if (!$user) {
            return false; // User not found
        }

        $roleId = $user->role_id;

        // Build the query to check compile_permission
        $query = CompilePermission::where('role_id', $roleId);

        // Add condition based on which ID is provided
        if ($checkPermissionId !== null) {
            $query->where('check_permission_id', $checkPermissionId);
        } elseif ($reqestCategoryId !== null) {
            $query->where('reqestcategory_id', $reqestCategoryId);
        } else {
            return false; // Neither ID provided
        }

        return $query->exists();
    }
}