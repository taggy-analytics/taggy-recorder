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

Route::post('testing/connected-to-mothership', [\App\Http\Controllers\Api\TestingController::class, 'connectedToMothership']);

Route::get('recorder/i-am-here', [\App\Http\Controllers\Api\RecorderController::class, 'iAmHere']);
Route::get('recorder/info', [\App\Http\Controllers\Api\RecorderController::class, 'info']);
Route::post('recorder/update-software', [\App\Http\Controllers\Api\RecorderController::class, 'updateSoftware']);
Route::post('recorder/installation-finished', [\App\Http\Controllers\Api\RecorderController::class, 'installationFinished']);
Route::post('recorder/refresh-app-key', [\App\Http\Controllers\Api\RecorderController::class, 'refreshAppKey']);
Route::get('recorder/get-public-key', [\App\Http\Controllers\Api\RecorderController::class, 'getPublicKey']);
Route::get('recorder/network-status', [\App\Http\Controllers\Api\RecorderController::class, 'networkStatus']);

// Route::get('recorder/vpn/status', [\App\Http\Controllers\Api\RecorderController::class, 'vpnStatus']);
// Route::post('recorder/vpn/config', [\App\Http\Controllers\Api\RecorderController::class, 'setVpnConfig']);
// Route::post('recorder/vpn/start', [\App\Http\Controllers\Api\RecorderController::class, 'startVpn']);
// Route::post('recorder/vpn/stop', [\App\Http\Controllers\Api\RecorderController::class, 'stopVpn']);

Route::post('recorder/tokens', [\App\Http\Controllers\Api\RecorderController::class, 'tokens']);

Route::post('recorder/dot-env', [\App\Http\Controllers\Api\RecorderController::class, 'setDotEnv']);

Route::get('cameras', [\App\Http\Controllers\Api\CameraController::class, 'index']);
Route::get('cameras/{camera}', [\App\Http\Controllers\Api\CameraController::class, 'show']);
Route::put('cameras/{camera}', [\App\Http\Controllers\Api\CameraController::class, 'update']);
Route::get('cameras/{camera}/recording', [\App\Http\Controllers\Api\CameraController::class, 'currentRecording']);
Route::post('cameras/{camera}/recording/start', [\App\Http\Controllers\Api\CameraController::class, 'startRecording']);
Route::post('cameras/{camera}/recording/stop', [\App\Http\Controllers\Api\CameraController::class, 'stopRecording']);

Route::get('recordings', [\App\Http\Controllers\Api\RecordingController::class, 'index']);
Route::get('recordings/{recording}', [\App\Http\Controllers\Api\RecordingController::class, 'show']);


Route::post('entities/{entityId}/transactions/status', [\App\Http\Controllers\Api\TransactionController::class, 'status']);
Route::post('entities/{entityId}/transactions', [\App\Http\Controllers\Api\TransactionController::class, 'store']);

/*
Route::get('wifis', [\App\Http\Controllers\Api\WifiController::class, 'index']);
Route::post('wifis', [\App\Http\Controllers\Api\WifiController::class, 'store']);
Route::delete('wifis/{ssid}', [\App\Http\Controllers\Api\WifiController::class, 'destroy']);
Route::put('wifis/{ssid}/change-password', [\App\Http\Controllers\Api\WifiController::class, 'updatePassword']);
*/
