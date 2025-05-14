<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:4|max:60|unique:users,username',
            'password' => 'required|min:5|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = new User();
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->last_login_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
        ], 201);
    }

    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:4|max:60',
            'password' => 'required|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Administrator::where('username', $request->username)->first();
        $guard = 'admin';

        if (!$user) {
            $user = User::where('username', $request->username)->first();
            $guard = 'web';
        }

        if (!$user) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Username does not exist',
            ], 404);
        }

        if (!Auth::guard($guard)->attempt($request->only('username', 'password'))) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'username or password is incorrect',
            ], 401);
        }

        $user->last_login_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status' => 'success',
            'token' => $token,
        ], 200);
    }

    public function signout(Request $request)
    {
        
        $request->user()->Tokens()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully',
        ], 200);
    }
}
