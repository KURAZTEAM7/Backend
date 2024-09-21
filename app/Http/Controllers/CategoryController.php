<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(Category::all());
    }

    public function show(Request $request, Category $category)
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

        return response()->json([
            'category' => $category,
            'products' => $category->products()->paginate($fields['per_page']),
        ]);
    }
}
