<?php

namespace App\Http\Controllers\Api\Organization_Structure;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    public function index(): JsonResponse
    {
        $positions = Position::where('is_show', true)->with('creator')->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $positions
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $position = Position::with('creator')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $position
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'position_name' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $position = Position::create([
            'position_name' => $validated['position_name'],
            'remark' => $validated['remark'] ?? null,
            'user_id' => Auth::id(),
            'is_show' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $position->load('creator')
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'position_name' => 'required|string|max:255',
            'remark' => 'string|nullable',
        ]);

        $position = Position::findOrFail($id);
        $position->update([
            'position_name' => $validated['position_name'],
            'remark' => $validated['remark'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $position->load('creator')
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $position = Position::findOrFail($id);
        $position->update(['is_show' => false]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Position deleted successfully'
        ], 200);
    }
}