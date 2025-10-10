<?php

namespace App\Http\Controllers\Api\UserManagment;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\SubPermission;
use App\Models\CheckPermission;
use App\Models\CompilePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::query()
            ->with([
                'compilePermissions.permission',
                'compilePermissions.subPermission',
                'compilePermissions.checkPermission',
                'permissions'
            ])
            ->where('is_show', 1)
            ->orderBy('id', 'desc')
            ->get();

        $permissions = $this->formatPermissions();

        $subPermissions = SubPermission::with('checkPermissions')
            ->where('is_show', 1)
            ->get();
        $checkPermissions = CheckPermission::where('is_show', 1)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
                'permissions' => $permissions,
                'subPermissions' => $subPermissions,
                'checkPermissions' => $checkPermissions,
            ],
            'message' => 'Roles retrieved successfully.'
        ], 200);
    }

    private function formatPermissions()
    {
        $permissions = Permission::with('subPermissions.checkPermissions')
            ->where('is_show', 1)
            ->get();

        // Fetch direct check permissions for each permission
        $checkPermissionsByPermission = CheckPermission::where('is_show', 1)
            ->whereNull('sub_permission_id')
            ->get()
            ->groupBy('permission_id');

        foreach ($permissions as $permission) {
            $permissionId = $permission->id;
            $directChecks = $checkPermissionsByPermission->get($permissionId, collect());

            // Append direct check permissions to the permission level
            // Assuming we add a 'direct_check_permissions' key or merge into existing structure
            // Based on example, it seems check_permissions can be at permission level
            $permission->direct_check_permissions = $directChecks;

            // The sub_permissions already have their check_permissions loaded
        }

        return $permissions;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:100|unique:roles,role_name',
            'remark' => 'nullable|string',
            'is_show' => 'boolean',
            'permission_ids' => 'array|nullable',
            'sub_permission_ids' => 'array|nullable',
            'check_permission_ids' => 'array|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed.'
            ], 422);
        }

        $validated = $validator->validated();

        $role = Role::create([
            'role_name' => $validated['role_name'],
            'remark' => $validated['remark'] ?? null,
            'is_show' => $validated['is_show'] ?? true,
        ]);

        $this->saveCompilePermissions($role->id, $validated);

        return response()->json([
            'success' => true,
            'data' => $role,
            'message' => 'Role created successfully.'
        ], 201);
    }

    public function show($id)
    {
        $role = Role::with([
            'compilePermissions.permission',
            'compilePermissions.subPermission',
            'compilePermissions.checkPermission',
            'permissions'
        ])
            ->where('is_show', 1)
            ->findOrFail($id);

        // Optionally format permissions for show as well
        $role->permissions = $this->formatPermissions();

        return response()->json([
            'success' => true,
            'data' => $role,
            'message' => 'Role retrieved successfully.'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $role = Role::where('is_show', 1)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:100|unique:roles,role_name,' . $role->id,
            'remark' => 'nullable|string',
            'is_show' => 'boolean',
            'permission_ids' => 'array|nullable',
            'sub_permission_ids' => 'array|nullable',
            'check_permission_ids' => 'array|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed.'
            ], 422);
        }

        $validated = $validator->validated();

        $role->update([
            'role_name' => $validated['role_name'],
            'remark' => $validated['remark'] ?? null,
            'is_show' => $validated['is_show'] ?? true,
        ]);

        CompilePermission::where('role_id', $role->id)->delete();

        $this->saveCompilePermissions($role->id, $validated);

        return response()->json([
            'success' => true,
            'data' => $role,
            'message' => 'Role updated successfully.'
        ], 200);
    }

    public function destroy($id)
    {
        $role = Role::where('is_show', 1)->findOrFail($id);

        CompilePermission::where('role_id', $role->id)->delete();
        $role->update(['is_show' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.'
        ], 200);
    }

    private function saveCompilePermissions($roleId, $data)
    {
        $permissionIds = $data['permission_ids'] ?? [];
        $subPermissionIds = $data['sub_permission_ids'] ?? [];
        $checkPermissionIds = $data['check_permission_ids'] ?? [];

        foreach ($checkPermissionIds as $checkPermissionId) {
            $checkPermission = CheckPermission::where('is_show', 1)->find($checkPermissionId);
            if ($checkPermission) {
                CompilePermission::create([
                    'role_id' => $roleId,
                    'permission_id' => $checkPermission->permission_id,
                    'sub_permission_id' => $checkPermission->sub_permission_id,
                    'check_permission_id' => $checkPermissionId,
                ]);
            }
        }

        foreach ($permissionIds as $permissionId) {
            if (!CompilePermission::where('role_id', $roleId)->where('permission_id', $permissionId)->exists()) {
                $permission = Permission::where('is_show', 1)->find($permissionId);
                if ($permission) {
                    CompilePermission::create([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'sub_permission_id' => null,
                        'check_permission_id' => null,
                    ]);
                }
            }
        }

        foreach ($subPermissionIds as $subPermissionId) {
            $subPermission = SubPermission::where('is_show', 1)->find($subPermissionId);
            if ($subPermission && !CompilePermission::where('role_id', $roleId)->where('sub_permission_id', $subPermissionId)->exists()) {
                CompilePermission::create([
                    'role_id' => $roleId,
                    'permission_id' => $subPermission->permission_id,
                    'sub_permission_id' => $subPermissionId,
                    'check_permission_id' => null,
                ]);
            }
        }
    }
}