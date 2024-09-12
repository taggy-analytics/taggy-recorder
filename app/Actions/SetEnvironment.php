<?php

namespace App\Actions;

use App\Models\UserToken;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class SetEnvironment
{
    public function execute($environment)
    {
        if($environment == config('app.env')){
            throw new \Exception("Recorder is already running in {$environment} environment");
        }

        app(TruncateRecorder::class)->execute();

        DotenvEditor::setKey('APP_ENV', $environment);
        DotenvEditor::save();

        return true;
    }
}
