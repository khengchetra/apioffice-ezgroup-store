<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::where('is_show', true)
            ->with('creator')
            ->orderBy('id', 'desc')
            ->get()
            ->each(function ($product) {
                if ($product->image) {
                    $product->image_url = asset('storage/ProductManager/Product/' . $product->image);
                }
            });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $product = Product::with('creator')->findOrFail($id);
        
        if ($product->image) {
            $product->image_url = asset('storage/ProductManager/Product/' . $product->image);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255|unique:products',
            'name_kh' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_cn' => 'nullable|string|max:255',
            'category_id' => [
                'nullable',
                'string',
                'regex:/^(\d+)(,\d+)*$/',
                function ($attribute, $value, $fail) {
                    $categoryIds = array_filter(explode(',', $value));
                    foreach ($categoryIds as $id) {
                        if (!\App\Models\Category::where('id', $id)->exists()) {
                            $fail("The category ID {$id} does not exist.");
                        }
                    }
                },
            ],
            'qty' => 'required|integer|min:0',
            'remark' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['is_show'] = true;

        if ($request->hasFile('image')) {
            $data['image'] = $this->saveImage($request->file('image'), 'ProductManager/Product');
        }

        $product = Product::create($data);
        $product->load('creator');

        if ($product->image) {
            $product->image_url = asset('storage/ProductManager/Product/' . $product->image);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'message' => 'Product created successfully'
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'product_code' => 'sometimes|required|string|max:255|unique:products,product_code,' . $id,
            'name_kh' => 'sometimes|nullable|string|max:255',
            'name_en' => 'sometimes|nullable|string|max:255',
            'name_cn' => 'sometimes|nullable|string|max:255',
            'category_id' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^(\d+)(,\d+)*$/',
                function ($attribute, $value, $fail) {
                    $categoryIds = array_filter(explode(',', $value));
                    foreach ($categoryIds as $id) {
                        if (!\App\Models\Category::where('id', $id)->exists()) {
                            $fail("The category ID {$id} does not exist.");
                        }
                    }
                },
            ],
            'qty' => 'sometimes|required|integer|min:0',
            'remark' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete('ProductManager/Product/' . $product->image);
            }
            $data['image'] = $this->saveImage($request->file('image'), 'ProductManager/Product');
        }

        $product->update($data);
        $product->load('creator');

        if ($product->image) {
            $product->image_url = asset('storage/ProductManager/Product/' . $product->image);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product,
            'message' => 'Product updated successfully'
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_show' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ], 200);
    }

    public function checkProductCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255',
            'product_id' => 'sometimes|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Product::where('product_code', $request->product_code);
        if ($request->filled('product_id')) {
            $query->where('id', '!=', $request->product_id);
        }

        $exists = $query->exists();

        return response()->json([
            'status' => 'success',
            'exists' => $exists,
            'message' => $exists ? 'Product code already taken' : 'Product code available',
        ], 200);
    }

    private function saveImage($file, $path): string
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $randomString = Str::random(100);
        $extension = $file->getClientOriginalExtension();
        $filename = "{$timestamp}_{$randomString}.{$extension}";

        $file->storeAs($path, $filename, 'public');

        return $filename;
    }
}