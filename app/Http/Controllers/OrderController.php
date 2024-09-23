<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * @authenticated
     **/
    public function store(Request $request): JsonResponse {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fields = $validator->validated();

        $product = Product::find($fields['product_id']);
        $fields['price_when_ordered'] = $product->price;
        $fields['user_id'] = auth()->user()->id;
        $fields['status'] = 'Pending';

        $order = Order::create($fields);

        return response()->json([
            'message' => 'Product added successfuly',
        ], 201);
    }
}
