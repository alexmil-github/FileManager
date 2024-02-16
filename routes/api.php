<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::post('/registration', [UserController::class, 'register']);
Route::post('/authorization', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout'])->middleware('auth:api');

Route::get('/unauth', function ()
{
    return response()->json([
       'message' => "Login failed"
    ], 403);
})->name('login');

Route::get('/files/shared', [\App\Http\Controllers\AccessController::class, 'shared'])->middleware('auth:api');

Route::get('/files/disk', [\App\Http\Controllers\AccessController::class, 'disk'])->middleware('auth:api');

Route::resource('files', \App\Http\Controllers\FileController::class)->middleware('auth:api');

Route::post('/files/{file}/access', [\App\Http\Controllers\AccessController::class, 'addRight'])->middleware('auth:api');

Route::delete('/files/{file}/access', [\App\Http\Controllers\AccessController::class, 'deleteRight'])->middleware('auth:api');


