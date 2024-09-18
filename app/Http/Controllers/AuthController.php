<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Todo: Implement uploading profile pics
    public function register(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|confirmed|min:8|max:64',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
        ]);

        if ($validator->fails()) {
            // Return a response with the validation error message
            $response = [
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ];

            return response($response, 422);
        }

        $fields = $validator->validated();

        if ($request->hasFile('picture')) {
            $cloudinaryPicture = $request->file('picture')->storeOnCloudinary('user_picture');
            $url = $cloudinaryPicture->getSecurePath();
            $id = $cloudinaryPicture->getPublicId();
            $fields['picture'] = $url;
            $fields['picture_public_id'] = $id;
        }

        $user = User::create($fields);

        $token = $user->createToken('ecommerce')->plainTextToken;

        $response = [
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ];

        return response($response, 201);
    }

    public function login(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            // Return a response with the validation error message
            $response = [
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ];
            
            return response($response, 422);
        }
        
        $fields = $validator->validated();
        $user = User::where('email', $fields['email'])->first();
        
        if (! $user || ! Hash::check($fields['password'], $user->password)) {
            return response(['message' => 'Invalid credentials'], 401);
        }
        
        $token = $user->createToken('ecommerce')->plainTextToken;
        
        $response = [
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token,
        ];
        
        return response($response, 201);
    }
    
    /**
     * @authenticated
     **/
    public function logout(Request $request): Response
    {
        if (! auth()->check()) {
            return response(['message' => 'Unauthenticated'], 401);
        }

        auth()->user()->tokens()->delete();

        return response(['message' => 'Logout succesful'], 401);
    }

    // TODO: implement allowing users to logout from other devices
}
