<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::get('status', [\App\Http\Controllers\Api\StatusController::class, 'getStatus']);
Route::get('cameras', [\App\Http\Controllers\Api\CameraController::class, 'index']);
*/

Route::get('resources', [\App\Http\Controllers\Api\ResourcesController::class, 'index']);

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
