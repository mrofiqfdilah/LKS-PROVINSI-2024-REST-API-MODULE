<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'full_name' => 'required',
        'bio' => 'required|max:100',
        'username' => 'required|min:3|unique:users|regex:/^[a-zA-Z0-9_.]+$/',
        'password' => 'required|min:6',
        'is_private' => 'boolean'
        ]);

        if($validator->fails()){
            return response()->json([
            'message' => 'Invalid field',
            'errors' => $validator->errors()
            ], 422);
        }

        $Private = $request->is_private == 1 ? true : false;

        $user = new User;
        $user->full_name = $request->full_name;
        $user->bio = $request->bio;
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->is_private = $Private;
        $user->save();

        $token = $user->createToken('user_tokens')->plainTextToken;

        return response()->json([
            'message' => 'Register success',
            'token' => $token,
            'user' => [
                'full_name' => $user->full_name,
                'bio' => $user->bio,
                'username' => $user->username,
                'is_private' => $user->is_private,
                'id' => $user->id,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $hanya = $request->only('username','password');

        if(Auth::attempt($hanya)){
            $user = Auth::user();

            $token = $user->createToken('user_tokens')->plainTextToken;

            return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'bio' => $user->bio,
                'is_private' => $user->is_private,
                'created_at' => $user->created_at,
            ],
            ], 200);
        }

        return response()->json([
        'message' => 'Wrong username or password'
        ], 401);
    }

  
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout success'
        ], 200);     
    }
}
