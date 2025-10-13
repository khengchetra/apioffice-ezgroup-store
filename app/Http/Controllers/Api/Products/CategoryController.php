<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::where('is_show', true)->with('creator')->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $category = Category::with('creator')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $category
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_category' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $category = Category::create([
            'name_category' => $validated['name_category'],
            'remark' => $validated['remark'] ?? null,
            'user_id' => Auth::id(),
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
            'name_category' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'name_category' => $validated['name_category'],
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $category->load('creator')
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->update(['is_show' => false]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ], 200);
    }
}