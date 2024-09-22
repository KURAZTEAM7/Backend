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
        return response()->json(Category::all(), 200);
    }

    public function show(Category $category)
    {
        return response()->json([
            'category' => $category,
            'products' => $category->products()->get(),
        ], 200);
    }
}
