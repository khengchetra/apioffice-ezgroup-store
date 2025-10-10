<?php

namespace App\Http\Controllers\Api\Organization_Structure;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $departments = Department::where('is_show', true)->with('creator')->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $departments
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $department = Department::with('creator')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $department
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $department = Department::create([
            'department_name' => $validated['department_name'],
            'remark' => $validated['remark'] ?? null,
            'user_id' => Auth::id(),
            'is_show' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $department->load('creator')
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $department = Department::findOrFail($id);
        $department->update([
            'department_name' => $validated['department_name'],
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $department->load('creator')
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $department = Department::findOrFail($id);
        $department->update(['is_show' => false]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Department deleted successfully'
        ], 200);
    }
}