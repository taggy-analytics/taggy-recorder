<?php

namespace App\Actions;

use App\Models\UserToken;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ResetRecorder
{
    public function execute()
    {
        $userToken = UserToken::first();

        if(empty($userToken)) {
            dd('Recorder must have user token to be able to reset');
        }

        Artisan::call('migrate:fresh', ['--force' => true]);

        UserToken::create($userToken->toArray());

        Artisan::call('taggy:update-software');

        UserToken::truncate();

        Artisan::call('key:generate');

        File::deleteDirectory(storage_path('logs'), true);
        File::deleteDirectory(storage_path('app/public/recordings'), true);
    }
}
