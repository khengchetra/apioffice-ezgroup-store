<?php

namespace App\Http\Controllers\Api\service;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index(): JsonResponse
    {
        $branches = Branch::where('is_show', true)->with('creator')->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $branches
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $branch = Branch::with('creator')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $branch
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $branch = Branch::create([
            'branch_name' => $validated['branch_name'],
            'remark' => $validated['remark'] ?? null,
            'user_id' => Auth::id(),
            'is_show' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $branch->load('creator')
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $branch = Branch::findOrFail($id);
        $branch->update([
            'branch_name' => $validated['branch_name'],
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $branch->load('creator')
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $branch = Branch::findOrFail($id);
        $branch->update(['is_show' => false]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Branch deleted successfully'
        ], 200);
    }
}