<?php

namespace App\Actions;

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

        $environment = strtoupper($environment);
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_APP_KEY', DotenvEditor::getKey("PUSHER_MOTHERSHIP_{$environment}_APP_KEY")['value']);
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_HOST', DotenvEditor::getKey("PUSHER_MOTHERSHIP_{$environment}_HOST")['value']);
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_AUTH_URL', DotenvEditor::getKey("PUSHER_MOTHERSHIP_{$environment}_AUTH_URL")['value']);

        return true;
    }
}
