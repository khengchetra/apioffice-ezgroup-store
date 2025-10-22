<?php

namespace App\Http\Controllers\Api\Products\Attribute;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')
            ->where('is_show', true)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $attributes
        ], 200);
    }

    public function show($id)
    {
        $attribute = Attribute::with('values')
            ->where('is_show', true)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $attribute
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'values' => 'required|array',
            'values.*' => 'required|string|max:255',
            'hex_codes' => 'nullable|array',
            'hex_codes.*' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', // Validate hex codes
            'remark' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $attribute = Attribute::create([
                'name' => $request->name,
                'remark' => $request->remark,
                'is_show' => true
            ]);

            foreach ($request->values as $index => $value) {
                // Set hex_code only if provided in hex_codes array
                $hexCode = $request->has('hex_codes') && isset($request->hex_codes[$index])
                    ? $request->hex_codes[$index]
                    : null;

                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                    'hex_code' => $hexCode,
                    'is_show' => true
                ]);
            }

            DB::commit();

            $attribute->load('values');

            return response()->json([
                'status' => 'success',
                'data' => $attribute
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Attribute creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create attribute'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'values' => 'required|array',
            'values.*' => 'required|string|max:255',
            'hex_codes' => 'nullable|array',
            'hex_codes.*' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', // Validate hex codes
            'remark' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $attribute = Attribute::where('is_show', true)->findOrFail($id);

            $attribute->update([
                'name' => $request->name,
                'remark' => $request->remark
            ]);

            // Update existing values and add new ones
            $existingValues = AttributeValue::where('attribute_id', $id)
                ->where('is_show', true)
                ->pluck('value')
                ->toArray();

            $newValues = array_diff($request->values, $existingValues);
            $valuesToRemove = array_diff($existingValues, $request->values);

            // Soft delete removed values
            if (!empty($valuesToRemove)) {
                AttributeValue::where('attribute_id', $id)
                    ->whereIn('value', $valuesToRemove)
                    ->update(['is_show' => false]);
            }

            // Add or update values
            foreach ($request->values as $index => $value) {
                // Set hex_code only if provided in hex_codes array
                $hexCode = $request->has('hex_codes') && isset($request->hex_codes[$index])
                    ? $request->hex_codes[$index]
                    : null;

                $existingValue = AttributeValue::where('attribute_id', $id)
                    ->where('value', $value)
                    ->where('is_show', true)
                    ->first();

                if ($existingValue) {
                    $existingValue->update(['hex_code' => $hexCode]);
                } else {
                    AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $value,
                        'hex_code' => $hexCode,
                        'is_show' => true
                    ]);
                }
            }

            DB::commit();

            $attribute->load('values');

            return response()->json([
                'status' => 'success',
                'data' => $attribute
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Attribute update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update attribute'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $attribute = Attribute::where('is_show', true)->findOrFail($id);

            $attribute->update(['is_show' => false]);
            AttributeValue::where('attribute_id', $id)
                ->where('is_show', true)
                ->update(['is_show' => false]);

            return response()->json([
                'status' => 'success',
                'message' => 'Attribute deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Attribute deletion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete attribute'
            ], 500);
        }
    }
}