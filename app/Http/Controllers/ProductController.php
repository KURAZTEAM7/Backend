<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Fetch all products
    public function index()
    {
        return response()->json(Product::all(), 200);
    }

    // Create a new product
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'flexible_pricing' => 'required|boolean',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // image validation
            'barcode_upc' => 'nullable|string',
            'barcode_eac' => 'nullable|string',
            'product_availability' => 'required|boolean',
            'tags' => 'nullable|array',
            'company_id' => 'required|exists:companies,company_id',
            'category_id' => 'required|exists:categories,category_id',
        ]);

        $imageUrls = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $uploadedFileUrl = cloudinary()->upload($image->getRealPath())->getSecurePath();
                $imageUrls[] = $uploadedFileUrl;
            }
        }

        $validatedData['image_urls'] = $imageUrls;

        $product = Product::create($validatedData);

        return response()->json($product, 201);
    }

    // Update a product
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric',
            'flexible_pricing' => 'sometimes|required|boolean',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'images' => 'nullable|array',  // Accept an array of images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'barcode_upc' => 'nullable|string',
            'barcode_eac' => 'nullable|string',
            'product_availability' => 'sometimes|required|boolean',
            'tags' => 'nullable|array',
            'company_id' => 'sometimes|required|exists:companies,company_id',
            'category_id' => 'sometimes|required|exists:categories,category_id',
        ]);

        if ($request->hasFile('images')) {
            $imageUrls = [];
            foreach ($request->file('images') as $image) {
                $uploadedFileUrl = cloudinary()->upload($image->getRealPath())->getSecurePath();
                $imageUrls[] = $uploadedFileUrl;
            }
            $validatedData['image_urls'] = $imageUrls;
        }

        $product->update($validatedData);

        return response()->json($product, 200);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
