<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\File_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{

    /**
     * Загрузка файла
     */
    public function store(Request $request)
    {
        define("PATH", "uploads/");


        //Функция изменения имени, в случае уже имеющегося в каталоге
        function generateNewFileName($file)
        {
            $i = 1;
            $originalName = $file->getClientOriginalName();
            $newFileName = $originalName;
            while (Storage::disk('public')->exists(PATH . $newFileName)) {
                $newFileName = pathinfo($originalName, PATHINFO_FILENAME) . '(' . $i . ').' . pathinfo($originalName, PATHINFO_EXTENSION);
                $i++;
            }
            return $newFileName;
        }

        $input = [];
        $result = [];
        $files = $request->file('files');
        if ($files) {
            //Перебераем массив файлов, полученных с формы
            foreach ($files as $key => $file) {
                $newFileName = generateNewFileName($file);
                Storage::disk('public')->putFileAs(PATH, $file, $newFileName);

                $input['url'] = Storage::disk('public')->url(PATH . $newFileName);
                $input['name'] = $newFileName;
                $input['owner_id'] = Auth::id();
                $input['file_id'] = Str::random(10);
                $file = File::create($input);
                File_user::create([
                    'file_id' => $file->id,
                    'user_id' => Auth::id()
                ]);

                $result[$key]['success'] = true;
                $result[$key]['message'] = 'Success';
                $result[$key]['name'] = $input['name'];
                $result[$key]['url'] = $input['url'];
                $result[$key]['file_id'] = $input['file_id'];
            }
            return response()->json(
                $result,
            );
        }


    }

    /**
     * Скачивание файла
     */
    public function show($file_id)
    {
        //Попытка доступа к несуществующему объекту
        $file = File::where('file_id', $file_id)->first();
        if (!$file) {
            return response()->json(['message' => 'Not found'], 404);
        }

        //Попытка скачать файл к которому нет доступа
        $access = File_user::where('file_id', $file->id)->where('user_id', Auth::id())->first();
        if (!$access) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        return Storage::disk('public')->url('uploads/' . $file->name);
    }


    /**
     * Изменение имени файла
     */
    public function update(Request $request, $file_id)
    {
        //Попытка доступа к несуществующему объекту
        $file = File::where('file_id', $file_id)->first();
        if (!$file) {
            return response()->json(['message' => 'Not found'], 404);
        }

        //Попытка изменить файл к которому нет доступа
        if ($file->owner_id != Auth::id()) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

        //Проверяем, если файл имеется в каталоге, то переименовываем с помощью move()
        if (Storage::disk('public')->exists('uploads/'.$file->name)) {
            $extension = pathinfo($file->name, PATHINFO_EXTENSION);

            Storage::disk('public')->move('uploads/' . $file->name, 'uploads/' . $request->name .'.'. $extension);
            $file->name = $request->name .".".$extension;
            $file->url = Storage::disk('public')->url('uploads/' . $request->name . '.'. $extension);
            $file->save();

            return response()->json([
               'success'=> true,
               'message' => 'Renamed'
            ]);
        }
    }

    /**
     * Удаление файла
     */
    public function destroy($file_id)
    {
        //Попытка доступа к несуществующему объекту
        $file = File::where('file_id', $file_id)->first();
        if (!$file) {
            return response()->json(['message' => 'Not found'], 404);
        }

        //Попытка удалить файл к которому нет доступа
        if ($file->owner_id != Auth::id()) {
            return response()->json(['message' => 'Forbidden for you'], 403);
        }

            $fileName = $file->name;
            $filePath = 'uploads/' . $fileName;

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
                $file->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'File already deleted'
                ], 200);
            }
    }
}
