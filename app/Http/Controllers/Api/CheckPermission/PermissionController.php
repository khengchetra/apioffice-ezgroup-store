<?php

namespace App\Http\Controllers\Api\CheckPermission;

use Illuminate\Http\Request;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function checkPermission(Request $request)
    {
        $userId = Auth::id(); // Get the authenticated user's ID
        $checkPermissionId = $request->input('check_permission_id'); // Get check_permission_id from request

        // Check if the user has the specified permission using the service
        $hasPermission = $this->permissionService->checkPermission($userId, $checkPermissionId);

        return response()->json([
            'hasPermission' => $hasPermission
        ]);
    }
}