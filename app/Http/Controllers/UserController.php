<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:3|regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[a-zA-Z\d]+$/',
            'first_name' => 'required',
            'last_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
//            'password' => $request->password,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name
        ]);

        $user->generateToken();

        return response()->json([
            'success' => true,
            'message' => "Success",
            'token' => $user->api_token
        ]);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $user->generateToken();

            return [
                'success' => true,
                'message' => "Success",
                'token' => $user->api_token
            ];
        }
        return response()->json([
            'success' => false,
            'message' => "Login failed",
        ], 401);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->api_token = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Logout"
        ]);
    }
}
