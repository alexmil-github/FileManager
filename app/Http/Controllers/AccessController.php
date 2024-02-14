<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccessResource;
use App\Models\File;
use App\Models\File_user;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessController extends Controller
{
    public function addRight(Request $request, $file_id)
    {
        $file = File::where('file_id', $file_id)->first();

        if (!$file) {
            return response()->json(['message' => 'Not found'], 404);
        }
        if ($file->owner_id != Auth::id()) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        $user_id = User::where('email', $request->email)->first()->id;

        if(File_user::where('file_id', $file->id)->where('user_id', $user_id)->first()) {
            return response()->json(['message' => 'Access is already available'], 403);
        }

        File_user::create([
            'file_id' => $file->id,
            'user_id' => $user_id
        ]);

        $users = File_user::where('file_id', $file->id)->select('user_id')->get();

        $access = [];

        foreach ($users as $key => $user) {
            $user = User::find($user->user_id);
            $access[$key] = [
                "fullname" => $user->first_name. " ".$user->last_name,
                "email" => $user->email,
                "type" => ($user->id == Auth::id() ? 'author' : 'co-author')
            ];
        }

        return $access;

    }
}
