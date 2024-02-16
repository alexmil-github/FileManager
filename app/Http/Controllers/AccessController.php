<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\File_user;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessController extends Controller
{
    //Добавление прав
    public function addRight(Request $request, $file_id)
    {
        //Попытка доступа к несуществующему объекту
        $file = File::where('file_id', $file_id)->first();
        if (!$file) {
            return response()->json(['message' => 'Not found'], 404);
        }
        //Попытка дать права файлу, к которому нет доступа
        if ($file->owner_id != Auth::id()) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        $user_id = User::where('email', $request->email)->first()->id;

        //Проверяем, что возможно уже есть доступ к этому файлу для пользователя
        if (File_user::where('file_id', $file->id)->where('user_id', $user_id)->first()) {
            return response()->json(['message' => 'Access is already available'], 403);
        }

        File_user::create([
            'file_id' => $file->id,
            'user_id' => $user_id
        ]);

        return $this->user_access($file);

    }

    //Удаление прав
    public function deleteRight(Request $request, $file_id)
    {
        //Попытка доступа к несуществующему объекту
        $file = File::where('file_id', $file_id)->first();
        $user = User::where('email', $request->email)->first();
        if (!$file || !$user) {
            return response()->json(['message' => 'Not found'], 404);
        }

        //Попытка удалить файл, к которому нет доступа
        if ($file->owner_id != Auth::id()) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        //Попытка удаления доступ у самого себя
        if (Auth::user()->email == $request->email) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        $access = File_user::where('file_id', $file->id)->where('user_id', $user->id)->first();
        //Попытка удаления доступа у пользователя, которого нет в списке соавторов
        if (!$access) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $access->delete();

        //Формируем ответ
        $users = File_user::where('file_id', $file->id)->select('user_id')->get();
        $result = [];
        foreach ($users as $key => $user) {
            $user = User::find($user->user_id);
            $result[$key] = [
                "fullname" => $user->first_name . " " . $user->last_name,
                "email" => $user->email,
                "type" => ($user->id == Auth::id() ? 'author' : 'co-author')
            ];
        }

        return $result;


    }

    //Просмотр файлов пользователя
    public function disk()
    {
        $files = File::where('owner_id', Auth::id())->get();
        $result = [];

        foreach ($files as $key => $file) {
            $result[$key] = [
                'file_id' => $file->file_id,
                'name' => $file->name,
                'url' => $file->url,
                'access' =>  $this->user_access($file)
            ];
        }
        return $result;


    }

    //Просмотр файлов, к которым имеет доступ пользователь
    public function shared()
    {
        $files = File_user::where('user_id', Auth::id())->get();
        $result = [];
        foreach ($files as $key => $file) {
            $result[$key] = [
                'file_id' => File::find($file->file_id)->file_id,
                'name' => File::find($file->file_id)->name,
                'url' => File::find($file->file_id)->url
            ];
        }
        return $result;

    }

    private function user_access($file)
    {
        $users = File_user::where('file_id', $file->id)->select('user_id')->get();

        $result = [];

        foreach ($users as $key => $user) {
            $user = User::find($user->user_id);
            $result[$key] = [
                "fullname" => $user->first_name . " " . $user->last_name,
                "email" => $user->email,
                "type" => ($user->id == Auth::id() ? 'author' : 'co-author')
            ];
        }
        return $result;
    }
}
