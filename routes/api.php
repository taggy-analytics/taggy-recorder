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

Route::get('recorder/system-id', [\App\Http\Controllers\Api\RecorderController::class, 'systemId']);
Route::get('recorder/update-software', [\App\Http\Controllers\Api\RecorderController::class, 'updateSoftware']);
Route::post('recorder/installation-finished', [\App\Http\Controllers\Api\RecorderController::class, 'installationFinished']);
Route::post('recorder/refresh-app-key', [\App\Http\Controllers\Api\RecorderController::class, 'refreshAppKey']);

Route::get('recorder/vpn/status', [\App\Http\Controllers\Api\RecorderController::class, 'vpnStatus']);
Route::post('recorder/vpn/config', [\App\Http\Controllers\Api\RecorderController::class, 'setVpnConfig']);
Route::post('recorder/vpn/start', [\App\Http\Controllers\Api\RecorderController::class, 'startVpn']);
Route::post('recorder/vpn/stop', [\App\Http\Controllers\Api\RecorderController::class, 'stopVpn']);

Route::get('router/password', [\App\Http\Controllers\Api\RouterController::class, 'getPassword']);

Route::get('cameras', [\App\Http\Controllers\Api\CameraController::class, 'index']);
Route::get('cameras/{camera}', [\App\Http\Controllers\Api\CameraController::class, 'show']);
Route::put('cameras/{camera}', [\App\Http\Controllers\Api\CameraController::class, 'update']);
Route::get('cameras/{camera}/recording', [\App\Http\Controllers\Api\CameraController::class, 'currentRecording']);
Route::post('cameras/{camera}/recording/start', [\App\Http\Controllers\Api\CameraController::class, 'startRecording']);
Route::post('cameras/{camera}/recording/stop', [\App\Http\Controllers\Api\CameraController::class, 'stopRecording']);

Route::get('recordings', [\App\Http\Controllers\Api\RecordingController::class, 'index']);
Route::get('recordings/{recording}', [\App\Http\Controllers\Api\RecordingController::class, 'show']);
Route::put('recordings/{recording}', [\App\Http\Controllers\Api\RecordingController::class, 'update']);

Route::post('transactions/status', [\App\Http\Controllers\Api\TransactionController::class, 'status']);
Route::post('transactions', [\App\Http\Controllers\Api\TransactionController::class, 'store']);


/*
Route::get('scene-containers', [\App\Http\Controllers\Api\SceneContainerController::class, 'index']);
Route::post('scene-containers', [\App\Http\Controllers\Api\SceneContainerController::class, 'store']);
Route::get('scene-containers/{sceneContainer}', [\App\Http\Controllers\Api\SceneContainerController::class, 'show']);

Route::get('scenes', [\App\Http\Controllers\Api\SceneController::class, 'index']);
Route::post('scenes', [\App\Http\Controllers\Api\SceneController::class, 'store']);
Route::get('scenes/{scene}', [\App\Http\Controllers\Api\SceneController::class, 'show']);
Route::put('scenes/{scene}', [\App\Http\Controllers\Api\SceneController::class, 'update']);
Route::delete('scenes/{scene}', [\App\Http\Controllers\Api\SceneController::class, 'delete']);
Route::get('scenes/{scene}/recordings/{recording}/video.mp4', [\App\Http\Controllers\Api\SceneController::class, 'download'])->name('scenes.download');
*/

/*
Route::get('wifis', [\App\Http\Controllers\Api\WifiController::class, 'index']);
Route::post('wifis', [\App\Http\Controllers\Api\WifiController::class, 'store']);
Route::delete('wifis/{ssid}', [\App\Http\Controllers\Api\WifiController::class, 'destroy']);
Route::put('wifis/{ssid}/change-password', [\App\Http\Controllers\Api\WifiController::class, 'updatePassword']);
*/
