<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * @authenticated
     **/
    public function orders(): JsonResponse
    {
        return response()->json(auth()->user()->orders()->get(), 200);
    }
}
