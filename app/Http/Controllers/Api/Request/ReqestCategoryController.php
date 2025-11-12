<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use App\Models\ReqestCategory;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReqestCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ReqestCategory::where('is_show', true)->with('creator')->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $category = ReqestCategory::with('creator')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $category
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $category = ReqestCategory::create([
            'name' => $validated['name'],
            'remark' => $validated['remark'] ?? null,
            'user_id' => Auth::id(),
            'is_active' => false,
            'is_show' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $category->load('creator')
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $category = ReqestCategory::findOrFail($id);
        $category->update([
            'name' => $validated['name'],
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $category->load('creator')
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $category = ReqestCategory::findOrFail($id);
        $category->update(['is_show' => false]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'ReqestCategory deleted successfully'
        ], 200);
    }

    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    // API: Get name and id where is_show = true and is_active = true with permission check
    public function getActiveCategories(): JsonResponse
    {
        $userId = Auth::id();

        $categories = ReqestCategory::where('is_show', true)
            ->where('is_active', true)
            ->select('id', 'name', 'remark')
            ->orderBy('id', 'desc')
            ->get()
            ->filter(function ($category) use ($userId) {
                // Check if the user has permission for this reqestcategory_id
                return $this->permissionService->checkPermission($userId, null, $category->id);
            })
            ->map(function ($category) {
                return [
                    'id'    => $category->id,
                    'value' => $category->name,
                    'label' => $category->remark
                ];
            })
            ->values(); // Reset collection keys after filtering

        return response()->json([
            'status' => 'success',
            'data'   => $categories
        ], 200);
    }

    // API ថ្មីទី 2: Update is_active status
    public function updateActiveStatus($id): JsonResponse
    {
        $category = ReqestCategory::findOrFail($id);
        
        $category->update([
            'is_active' => !$category->is_active
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Active status updated successfully',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'is_active' => $category->is_active
            ]
        ], 200);
    }
}