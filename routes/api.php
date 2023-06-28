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

Route::get('cameras', [\App\Http\Controllers\Api\CameraController::class, 'index']);
Route::get('cameras/{camera}', [\App\Http\Controllers\Api\CameraController::class, 'show']);
Route::put('cameras/{camera}', [\App\Http\Controllers\Api\CameraController::class, 'update']);
Route::get('cameras/{camera}/recording', [\App\Http\Controllers\Api\CameraController::class, 'currentRecording']);
Route::post('cameras/{camera}/recording/start', [\App\Http\Controllers\Api\CameraController::class, 'startRecording']);
Route::post('cameras/{camera}/recording/stop', [\App\Http\Controllers\Api\CameraController::class, 'stopRecording']);

Route::get('recordings', [\App\Http\Controllers\Api\RecordingController::class, 'index']);
Route::get('recordings/{recording}', [\App\Http\Controllers\Api\RecordingController::class, 'show']);
Route::get('recordings/{recording}/video-vod.m3u8', [\App\Http\Controllers\Api\RecordingController::class, 'videoVod'])->name('recording.video-vod');
Route::put('recordings/{recording}', [\App\Http\Controllers\Api\RecordingController::class, 'update']);

Route::get('scenes', [\App\Http\Controllers\Api\SceneController::class, 'index']);
Route::post('scenes', [\App\Http\Controllers\Api\SceneController::class, 'store']);
Route::put('scenes/{scene}', [\App\Http\Controllers\Api\SceneController::class, 'update']);
Route::delete('scenes/{scene}', [\App\Http\Controllers\Api\SceneController::class, 'delete']);
Route::get('scenes/{scene}/recordings/{recording}/video.mp4', [\App\Http\Controllers\Api\SceneController::class, 'download'])->name('scenes.download');

Route::get('wifis', [\App\Http\Controllers\Api\WifiController::class, 'index']);
Route::post('wifis', [\App\Http\Controllers\Api\WifiController::class, 'store']);
Route::delete('wifis/{ssid}', [\App\Http\Controllers\Api\WifiController::class, 'destroy']);
Route::put('wifis/{ssid}/change-password', [\App\Http\Controllers\Api\WifiController::class, 'updatePassword']);

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
