<?php

use App\Http\Controllers\Api\RecordingController;
use App\Http\Controllers\EnableApiController;
use App\Http\Controllers\ImageController;
use App\Livewire\InitialSetup;
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

Route::get('storage/recordings/{recording}/{key}/video/video-vod.m3u8', [RecordingController::class, 'videoVod'])->name('recording.video-vod');
Route::get('storage/recordings/{recording}/{key}/scenes/{startTime}/{duration}/{name}', [RecordingController::class, 'downloadScene'])->name('recording.download-scene');

Route::get('docs/api/enable/{key}', EnableApiController::class);

Route::get('images/{image}', ImageController::class)->name('image');

Route::get('initial-setup', InitialSetup::class)->name('initial-setup')->withoutMiddleware(\App\Http\Middleware\CheckBoxIsSetUp::class);
