<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        define("PATH", "uploads/");


        function generateNewFileName($file)
        {
            $i = 1;
            $originalName = $file->getClientOriginalName();
            $newFileName = $originalName;
            while (Storage::disk('public')->exists(PATH . $newFileName)) {
                $newFileName = pathinfo($originalName, PATHINFO_FILENAME) . '('. $i .').' . pathinfo($originalName, PATHINFO_EXTENSION);
                $i++;
            }
            return $newFileName;
        }

        $input = [];
        $result = [];
        $files = $request->file('files');
        if ($files) {
            foreach ($files as $key => $file) {
                $newFileName = generateNewFileName($file);
                $url = Storage::disk('public')->putFileAs(PATH, $file, $newFileName);

                $input['url'] = Storage::disk('public')->url(PATH . $newFileName);;
                $input['name'] = $newFileName;
                $input['owner_id'] = Auth::id();
                $input['file_id'] = Str::random(10);
                File::create($input);

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
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($file_id)
    {
        $file = File::where('file_id', $file_id)->first();
        $fileName = $file->name;
        $filePath = 'uploads/' . $fileName;


        if (Storage::disk('public')->exists($filePath)) {
           Storage::disk('public')->delete($filePath);
            return response()->json(['message' => 'File deleted successfully.']);
        } else {
            return response()->json(['message' => 'File not found.']);
        }

    }
}
