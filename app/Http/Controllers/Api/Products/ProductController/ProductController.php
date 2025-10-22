<?php

namespace App\Http\Controllers\Api\Products\ProductController;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttribute;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['variants.attributes.attributeValue.attribute'])
            ->where('is_show', true);

        // Search
        $searchQuery = $request->input('search_query');
        $searchField = $request->input('search_field', 'product_code');
        if ($searchQuery && in_array($searchField, ['product_code', 'name_kh', 'name_en', 'name_cn'])) {
            $query->where($searchField, 'LIKE', "%{$searchQuery}%");
        }

        $perPage = $request->input('per_page', 10);
        $products = $query->orderBy('id', 'desc')->paginate($perPage);

        $products->getCollection()->transform(function ($product) {
            if ($product->image) {
                $product->image_url = asset('storage/ProductManager/Product/' . $product->image);
            }
            $product->variants->each(function ($variant) {
                if ($variant->image) {
                    $variant->image_url = asset('storage/ProductManager/product_variants/' . $variant->image);
                }
            });
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'total_pages' => $products->lastPage(),
                'total_items' => $products->total(),
                'per_page' => $products->perPage(),
            ]
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $product = Product::with(['variants.attributes.attributeValue.attribute'])
            ->where('is_show', true)
            ->findOrFail($id);

        if ($product->image) {
            $product->image_url = asset('storage/ProductManager/Product/' . $product->image);
        }
        $product->variants->each(function ($variant) {
            if ($variant->image) {
                $variant->image_url = asset('storage/ProductManager/product_variants/' . $variant->image);
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255|unique:products',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp,tiff',
            'name_kh' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_cn' => 'nullable|string|max:255',
            'category_id' => 'nullable|array',
            'category_id.*' => 'exists:categories,id',
            'remark' => 'nullable|string',
            'is_active' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*' => 'exists:attribute_values,id',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp,tiff',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->saveImage($request->file('image'), 'ProductManager/Product');
            }

            $categoryIds = $request->category_id ?? [];
            $categoryString = !empty($categoryIds) ? implode(',', $categoryIds) : null;

            $product = Product::create([
                'product_code' => $request->product_code,
                'image' => $imagePath,
                'name_kh' => $request->name_kh ?? null,
                'name_en' => $request->name_en ?? null,
                'name_cn' => $request->name_cn ?? null,
                'category_id' => $categoryString,
                'user_id' => Auth::id(),
                'remark' => $request->remark ?? null,
                'is_active' => $request->boolean('is_active', false),
                'is_show' => true,
            ]);

            // ✅ FIXED: ALWAYS CREATE AT LEAST 1 VARIANT
            if ($request->filled('variants') && count($request->variants) > 0) {
                foreach ($request->variants as $variantData) {
                    $attributeValues = [];
                    if (!empty($variantData['attributes'])) {
                        $attributeValues = AttributeValue::whereIn('id', $variantData['attributes'])
                            ->pluck('value')
                            ->toArray();
                    }
                    
                    $sku = !empty($attributeValues) 
                        ? $request->product_code . '-' . implode('-', $attributeValues)
                        : $request->product_code . '-single';

                    $variantImagePath = null;
                    if (isset($variantData['image']) && $variantData['image']->isValid()) {
                        $variantImagePath = $this->saveImage($variantData['image'], 'ProductManager/product_variants');
                    }

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'image' => $variantImagePath,
                        'is_show' => true,
                    ]);

                    if (!empty($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attributeValueId) {
                            ProductVariantAttribute::create([
                                'variant_id' => $variant->id,
                                'attribute_value_id' => $attributeValueId,
                            ]);
                        }
                    }
                }
            } else {
                // ✅ CREATE SINGLE VARIANT IF NONE PROVIDED
                $sku = $request->product_code . '-single';
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'price' => 0,
                    'stock' => 0,
                    'image' => $imagePath,
                    'is_show' => true,
                ]);
            }

            DB::commit();

            $product->load(['variants.attributes.attributeValue.attribute']);
            $this->addImageUrls($product);

            return response()->json([
                'status' => 'success',
                'data' => $product,
                'message' => 'Product created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Product creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create product'
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255|unique:products,product_code,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp,tiff',
            'name_kh' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_cn' => 'nullable|string|max:255',
            'category_id' => 'nullable|array',
            'category_id.*' => 'exists:categories,id',
            'remark' => 'nullable|string',
            'is_active' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:product_variants,id',
            'variants.*.attributes' => 'nullable|array',
            'variants.*.attributes.*' => 'exists:attribute_values,id',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp,tiff',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $product = Product::where('is_show', true)->findOrFail($id);

            // ✅ PRODUCT IMAGE - KEEP OLD IF NO NEW
            $imagePath = $product->image; // DEFAULT: Old image
            if ($request->hasFile('image')) {
                // ONLY DELETE OLD IF NEW UPLOADED
                if ($imagePath) {
                    Storage::disk('public')->delete('ProductManager/Product/' . $imagePath);
                }
                $imagePath = $this->saveImage($request->file('image'), 'ProductManager/Product');
            }

            $categoryIds = $request->category_id ?? [];
            $categoryString = !empty($categoryIds) ? implode(',', $categoryIds) : null;

            $product->update([
                'product_code' => $request->product_code,
                'image' => $imagePath, // ✅ Uses old image if no new
                'name_kh' => $request->name_kh ?? null,
                'name_en' => $request->name_en ?? null,
                'name_cn' => $request->name_cn ?? null,
                'category_id' => $categoryString,
                'remark' => $request->remark ?? null,
                'is_active' => $request->boolean('is_active', $product->is_active),
            ]);

            // ✅ FIXED: Variants optional + KEEP OLD IMAGES + HANDLE IDs FOR UPDATES
            $sentVariantIds = [];
            if ($request->filled('variants') && count($request->variants) > 0) {
                foreach ($request->variants as $variantData) {
                    $attributeValues = [];
                    if (!empty($variantData['attributes'])) {
                        $attributeValues = AttributeValue::whereIn('id', $variantData['attributes'])
                            ->pluck('value')
                            ->toArray();
                    }
                    
                    $sku = !empty($attributeValues) 
                        ? $request->product_code . '-' . implode('-', $attributeValues)
                        : $request->product_code . '-single';

                    if (isset($variantData['id']) && $variantData['id']) {
                        // UPDATE EXISTING VARIANT
                        $variant = ProductVariant::where('id', $variantData['id'])
                            ->where('product_id', $product->id)
                            ->where('is_show', true)
                            ->first();

                        if ($variant) {
                            // ✅ VARIANT IMAGE - KEEP OLD IF NO NEW
                            $variantImagePath = $variant->image;
                            if (isset($variantData['image']) && $variantData['image']->isValid()) {
                                // DELETE OLD ONLY IF NEW UPLOADED
                                if ($variantImagePath) {
                                    Storage::disk('public')->delete('ProductManager/product_variants/' . $variantImagePath);
                                }
                                $variantImagePath = $this->saveImage($variantData['image'], 'ProductManager/product_variants');
                            }

                            $variant->update([
                                'sku' => $sku,
                                'price' => $variantData['price'],
                                'stock' => $variantData['stock'],
                                'image' => $variantImagePath,
                                'is_show' => true,
                            ]);

                            // Clear and add new attributes
                            ProductVariantAttribute::where('variant_id', $variant->id)->delete();
                            if (!empty($variantData['attributes'])) {
                                foreach ($variantData['attributes'] as $attributeValueId) {
                                    ProductVariantAttribute::create([
                                        'variant_id' => $variant->id,
                                        'attribute_value_id' => $attributeValueId,
                                    ]);
                                }
                            }

                            $sentVariantIds[] = $variant->id;
                        }
                    } else {
                        // CREATE NEW VARIANT
                        $variantImagePath = null;
                        if (isset($variantData['image']) && $variantData['image']->isValid()) {
                            $variantImagePath = $this->saveImage($variantData['image'], 'ProductManager/product_variants');
                        }

                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $sku,
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                            'image' => $variantImagePath,
                            'is_show' => true,
                        ]);

                        if (!empty($variantData['attributes'])) {
                            foreach ($variantData['attributes'] as $attributeValueId) {
                                ProductVariantAttribute::create([
                                    'variant_id' => $variant->id,
                                    'attribute_value_id' => $attributeValueId,
                                ]);
                            }
                        }

                        $sentVariantIds[] = $variant->id;
                    }
                }

                // ✅ DELETE REMOVED VARIANTS + THEIR IMAGES + ATTRIBUTES
                $variantsToRemove = ProductVariant::where('product_id', $product->id)
                    ->where('is_show', true)
                    ->whereNotIn('id', $sentVariantIds)
                    ->get();

                foreach ($variantsToRemove as $variant) {
                    if ($variant->image) {
                        Storage::disk('public')->delete('ProductManager/product_variants/' . $variant->image);
                    }
                    ProductVariantAttribute::where('variant_id', $variant->id)->delete();
                }

                ProductVariant::where('product_id', $product->id)
                    ->where('is_show', true)
                    ->whereNotIn('id', $sentVariantIds)
                    ->update(['is_show' => false]);
            } else {
                // ✅ NO VARIANTS PROVIDED - CREATE/UPDATE SINGLE VARIANT + KEEP PRODUCT IMAGE
                $existingVariants = ProductVariant::where('product_id', $id)->where('is_show', true)->get();
                
                // DELETE ALL EXISTING VARIANTS + IMAGES
                foreach ($existingVariants as $variant) {
                    if ($variant->image) {
                        Storage::disk('public')->delete('ProductManager/product_variants/' . $variant->image);
                    }
                    ProductVariantAttribute::where('variant_id', $variant->id)->delete();
                }
                ProductVariant::where('product_id', $id)->where('is_show', true)->update(['is_show' => false]);

                // CREATE SINGLE VARIANT WITH PRODUCT IMAGE
                $sku = $request->product_code . '-single';
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'price' => 0,
                    'stock' => 0,
                    'image' => $imagePath, // ✅ Uses product image (old or new)
                    'is_show' => true,
                ]);
            }

            DB::commit();

            $product->load(['variants.attributes.attributeValue.attribute']);
            $this->addImageUrls($product);

            return response()->json([
                'status' => 'success',
                'data' => $product,
                'message' => 'Product updated successfully'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Product update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update product'
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::where('is_show', true)->findOrFail($id);
        
        if ($product->image) {
            Storage::disk('public')->delete('ProductManager/Product/' . $product->image);
        }
        
        $variants = ProductVariant::where('product_id', $id)->where('is_show', true)->get();
        foreach ($variants as $variant) {
            if ($variant->image) {
                Storage::disk('public')->delete('ProductManager/product_variants/' . $variant->image);
            }
        }
        
        $product->update(['is_show' => false]);
        ProductVariant::where('product_id', $id)
            ->where('is_show', true)
            ->update(['is_show' => false]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ], 200);
    }

    private function saveImage($file, $path)
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $randomString = Str::random(100);
        $filename = "{$timestamp}_{$randomString}.webp";

        $mime = $file->getClientOriginalExtension();
        
        switch (strtolower($mime)) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($file->getPathname());
                break;
            case 'png':
                $image = imagecreatefrompng($file->getPathname());
                break;
            case 'webp':
                $image = imagecreatefromwebp($file->getPathname());
                break;
            case 'gif':
                $image = imagecreatefromgif($file->getPathname());
                break;
            case 'bmp':
                $image = imagecreatefromwbmp($file->getPathname());
                break;
            case 'tiff':
                $image = imagecreatefromtga($file->getPathname());
                break;
            default:
                throw new \Exception('Unsupported image type: ' . $mime);
        }

        $fullPath = storage_path('app/public/' . $path . '/' . $filename);
        imagewebp($image, $fullPath, 80);
        imagedestroy($image);

        return $filename;
    }

    private function addImageUrls($product)
    {
        if ($product->image) {
            $product->image_url = asset('storage/ProductManager/Product/' . $product->image);
        }
        $product->variants->each(function ($variant) {
            if ($variant->image) {
                $variant->image_url = asset('storage/ProductManager/product_variants/' . $variant->image);
            }
        });
    }

    public function Attribute(): JsonResponse
    {
        $attributes = Attribute::where('is_show', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'data' => $attributes
        ], 200);
    }

    public function AttributeValue(): JsonResponse
    {
        $attributeValues = AttributeValue::where('is_show', true)
            ->with('attribute:id,name')
            ->select('id', 'attribute_id', 'value', 'hex_code')
            ->orderBy('attribute_id')
            ->orderBy('value')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $attributeValues
        ], 200);
    }

    public function updateActiveStatus($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        
        $product->update([
            'is_active' => !$product->is_active
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Active status updated successfully',
            'data' => [
                'id' => $product->id,
                'name_en' => $product->name_en,
                'is_active' => $product->is_active
            ]
        ], 200);
    }

    public function checkProductCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required|string|max:255',
            'id' => 'nullable|integer|exists:products,id' // Optional: for excluding current product during update
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $productCode = $request->input('product_code');
        $id = $request->input('id'); // For edit mode, to exclude the current product

        $query = Product::where('product_code', $productCode);
        
        if ($id) {
            $query->where('id', '!=', $id); // Exclude the current product when updating
        }

        $exists = $query->exists();

        return response()->json([
            'status' => 'success',
            'exists' => $exists,
            'message' => $exists ? 'Product code already exists' : 'Product code is available'
        ], 200);
    }

    // ✅ GET RANDOM PRODUCTS
    public function randomProducts(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);

        $products = Product::where('is_show', true)
            ->where('is_active', true)
            ->inRandomOrder()
            ->select('id', 'product_code', 'name_kh', 'name_en', 'name_cn', 'remark', 'image')
            ->paginate($perPage);

        // បន្ថែម image_url
        $products->getCollection()->transform(function ($product) {
            $product->image_url = $product->image 
                ? asset('storage/ProductManager/Product/' . $product->image)
                : '';
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'total_pages' => $products->lastPage(),
                'total_items' => $products->total(),
                'per_page' => $products->perPage(),
            ]
        ], 200);
    }



    // ✅ SEARCH PRODUCTS (for mobile)
    public function searchProducts(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $keyword = $request->input('keyword');

        $query = Product::where('is_show', true)
            ->where('is_active', true)
            ->select('id', 'product_code', 'name_kh', 'name_en', 'name_cn', 'remark', 'image');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('product_code', 'LIKE', "%{$keyword}%")
                ->orWhere('name_kh', 'LIKE', "%{$keyword}%")
                ->orWhere('name_en', 'LIKE', "%{$keyword}%")
                ->orWhere('name_cn', 'LIKE', "%{$keyword}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate($perPage);

        // បន្ថែម image_url
        $products->getCollection()->transform(function ($product) {
            $product->image_url = $product->image 
                ? asset('storage/ProductManager/Product/' . $product->image)
                : null;
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'total_pages' => $products->lastPage(),
                'total_items' => $products->total(),
                'per_page' => $products->perPage(),
            ]
        ], 200);
    }


    
}