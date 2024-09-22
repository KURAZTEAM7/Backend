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
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fields = $validator->validated();
        $fields['per_page'] = $fields['per_page'] ?? 10;

        return response()->json(Product::paginate($fields['per_page']), 200);
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
            'flexible_pricing' => 'required|boolean|nullable',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // image validation
            'barcode_upc' => 'nullable|string|size:12',
            'barcode_eac' => 'nullable|string|size:13',
            'remaining_stock' => 'required|integer|min:0',
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
            ], 403);
        }

        $fields['vendor_id'] = $vendor->id;

        $imageUrls = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $cloudinaryPicture = $image->storeOnCloudinary('product_image');
                $url = $cloudinaryPicture->getSecurePath();
                $imageUrls[] = $url;
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
            ], 403);
        }

        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Nothing to delete',
            ], 200);
        }

        if ($product->vendor != $vendor) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
