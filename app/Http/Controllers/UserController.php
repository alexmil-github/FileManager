<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function register(Request $request)
    {
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name
        ]);

        $user->generateToken();

        return [
            'success' => true,
            'massage' => "Success",
            'token' => $user->api_token
        ];
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $user->generateToken();

            return [
                'success' => true,
                'massage' => "Success",
                'token' => $user->api_token
            ];
        }
        return response()->json([
            'success' => false,
            'massage' => "Login failed",
        ], 401);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->api_token = null;


        return [
            'success' => true,
            'massage' => "Logout"
        ];
    }
}
