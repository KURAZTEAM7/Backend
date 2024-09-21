<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Fetch all products
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:0',
            'per_page' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fields = $validator->validated();
        $fields['per_page'] = $fields['per_page'] ?? 10;

        return response()->json(Product::paginate($fields['per_page']));
    }

    /**
     * @authenticated
     **/
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'flexible_pricing' => 'required|boolean',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // image validation
            'barcode_upc' => 'nullable|string|size:12',
            'barcode_eac' => 'nullable|string|size:13',
            'product_availability' => 'required|boolean',
            'tags' => 'nullable|array',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fields = $validator->validated();

        $vendor = auth()->user()->vendor;
        if (! $vendor) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 422);
        }

        $fields['vendor_id'] = $vendor->id;

        $imageUrls = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $uploadedFileUrl = cloudinary()->upload($image->getRealPath())->getSecurePath();
                $imageUrls[] = $uploadedFileUrl;
            }
        }

        $fields['image_urls'] = $imageUrls;

        $product = Product::create($fields);

        return response()->json($product, 201);
    }

    /**
     * @authenticated
     **/
    public function destroy($id)
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 422);
        }

        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product does not exist',
            ], 422);
        }

        if ($product->vendor != $vendor) {
            return response()->json([
                'message' => 'This vendor cannot delete this product',
            ], 422);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
