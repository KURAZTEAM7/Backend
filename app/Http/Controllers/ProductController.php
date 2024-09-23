<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Product\ProductHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // Fetch all products
    public function index(): JsonResponse
    {
        return response()->json(Product::all(), 200);
    }

    public function show(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product cannot be found',
            ], 422);
        }

        $similar = ProductHelper::findSimilar($product);

        return response()->json([
            'product' => $product,
            'similar' => $similar,
        ]);
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

    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'barcode' => 'nullable|string|min:5|max:15',
            'query' => 'nullable|string|min:1',
            'min' => 'nullable|numeric|min:0',
            'max' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->filled('barcode')) {
            $barcodeQuery = $request->input('barcode');
            $products = ProductHelper::levenshtein_search($barcodeQuery, 'barcode_upc', 'barcode_eac');
            if (! $products) {
                return response()->json([], 200);
            }
            $url = url('/product/'.$products[0]['product_id']);

            return response()->json(['url' => $url]);
        }

        $query = $request->input('query');
        $minPrice = $request->input('min');
        $maxPrice = $request->input('max');

        if (! $query) {
            return response()->json([], 200);
        }

        // Convert query string into an array of tags
        $tags = explode(' ', $query);

        $results = Product::where(function ($query) use ($tags) {
            foreach ($tags as $tag) {
                $tag = strtolower($tag);
                $query->orWhereRaw('LOWER(tags) LIKE ?', ['%'.$tag.'%'])
                    ->orWhereRaw('LOWER(title) LIKE ?', ['%'.$tag.'%'])
                    ->orWhereRaw('LOWER(description) LIKE ?', ['%'.$tag.'%'])
                    ->orWhereRaw('LOWER(model) LIKE ?', ['%'.$tag.'%'])
                    ->orWhereRaw('LOWER(brand) LIKE ?', ['%'.$tag.'%']);
            }
        });

        if ($minPrice) {  // Apply price filtering if 'min' or 'max' are provided
            $results->where('price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $results->where('price', '<=', $maxPrice);
        }

        $results = $results->get();

        return response()->json([count($results), $results]);
    }

    /**
     * @authenticated
     **/
    public function destroy($id): JsonResponse
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
