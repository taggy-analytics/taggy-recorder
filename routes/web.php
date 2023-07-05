<?php

use App\Http\Controllers\Api\RecordingController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', \App\Http\Livewire\Cameras::class);
Route::get('/qr', QrCodeController::class);
Route::get('storage/recordings/{recording}/{key}/video/video-vod.m3u8', [RecordingController::class, 'videoVod'])->name('recording.video-vod');
