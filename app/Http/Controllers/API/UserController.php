<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getAdmins(Request $request)
    {
        $admins = Administrator::all();
        $adminId = $admins->pluck('id')->toArray();

        if (!in_array($request->user()->id, $adminId)) {
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not the administrator"
            ], 403);
        }

        return response()->json([
            'totalElements' => $admins->count(),
            'content' => $admins,
        ], 200);
    }

    public function addUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:4|max:60|unique:users,username',
            'password' => 'required|min:5|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $admin = Administrator::all();
        $adminId = $admin->pluck('id')->toArray();
        if (!in_array($request->user()->id, $adminId)) {
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not the administrator"
            ], 403);
        }

        $user = new User();
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'username' => $user->username,
        ], 201);
    }

    public function getUsers(Request $request)
    {
        $admin = Administrator::all();
        $adminId = $admin->pluck('id')->toArray();
        if (!in_array($request->user()->id, $adminId)) {
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not the administrator"
            ], 403);
        }

        $user = User::all();

        return response()->json([
            'totalElements' => $user->count(),
            'content' => $user,
        ], 200);
    }

    public function editUsers(Request $request, $id)
    {
        $admin = Administrator::all();
        $adminId = $admin->pluck('id')->toArray();
        if (!in_array($request->user()->id, $adminId)) {
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not the administrator"
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'not-found',
                'message' => 'User not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|min:4|max:60|unique:users,username',
            'password' => 'required|min:5|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'errors' => $validator->errors(),
            ], 422);
        }


        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'username' => $user->username,
        ], 200);
    }

    public function deleteUsers(Request $request, $id)
    {
        $admin = Administrator::all();
        $adminId = $admin->pluck('id')->toArray();
        if (!in_array($request->user()->id, $adminId)) {
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not the administrator"
            ], 403);
        }

        $user = User::where('id', $id)->whereNull('deleted_at')->first();

        if (!$user) {
            return response()->json([
                'status' => 'not-found',
                'message' => 'User not found',
            ], 404);
        }

        $user->delete();
        return response()->json([], 204)->header('Content-Type', 'plain/text');
    }
}
