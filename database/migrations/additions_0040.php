<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

return new class extends Migration
{
    public function up()
    {
        $this->removeFilesFromEnv([
            '# TEST CONFIGURATION',
            '# PROD CONFIGURATION',
            '# PUSHER_MOTHERSHIP_APP_KEY=AFbfwPxLCPqenDmLPWYV49iPAF',
            '# PUSHER_MOTHERSHIP_APP_KEY=WYV49iPAFbfwPxLCPqenDmLP',
            '# PUSHER_MOTHERSHIP_HOST=ws.websockets.taggy.cam',
            '# PUSHER_MOTHERSHIP_HOST=ws.websockets.test.taggy.cam',
            '# PUSHER_MOTHERSHIP_AUTH_URL=https://api-v2.taggy.cam/broadcasting/auth',
            '# PUSHER_MOTHERSHIP_AUTH_URL=https://api.test.taggy.cam/broadcasting/auth',
        ]);

        DotenvEditor::setKey('PUSHER_MOTHERSHIP_PRODUCTION_APP_KEY', 'AFbfwPxLCPqenDmLPWYV49iPAF');
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_PRODUCTION_HOST', 'ws.websockets.taggy.cam');
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_PRODUCTION_AUTH_URL', 'https://api-v2.taggy.cam/broadcasting/auth');

        DotenvEditor::setKey('PUSHER_MOTHERSHIP_TEST_APP_KEY', 'WYV49iPAFbfwPxLCPqenDmLP');
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_TEST_HOST', 'ws.websockets.test.taggy.cam');
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_TEST_AUTH_URL', 'https://api.test.taggy.cam/broadcasting/auth');

        DotenvEditor::setKey('PUSHER_MOTHERSHIP_DEMO_APP_KEY', 'WYV49iPAFbfwPxLCPqenDmLP');
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_DEMO_HOST', 'ws.websockets.test.taggy.cam');
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_DEMO_AUTH_URL', 'https://api.test.taggy.cam/broadcasting/auth');

        DotenvEditor::setKey('PUSHER_MOTHERSHIP_LOCAL_APP_KEY', 'WYV49iPAFbfwPxLCPqenDmLP');
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_LOCAL_HOST', 'ws.websockets.test.taggy.cam');
        DotenvEditor::setKey('PUSHER_MOTHERSHIP_LOCAL_AUTH_URL', 'https://api.test.taggy.cam/broadcasting/auth');

        DotenvEditor::save();
    }

    private function removeFilesFromEnv($linesToRemove)
    {
        $envFilePath = base_path('../../.env');

        $envContent = File::get($envFilePath);

        $updatedEnvContent = collect(explode(PHP_EOL, $envContent))
            ->reject(function ($line) use ($linesToRemove) {
                foreach ($linesToRemove as $remove) {
                    if (str_contains($line, $remove) || empty(trim($line))) {
                        return true;
                    }
                }
                return false;
            })
            ->implode(PHP_EOL);

        File::put($envFilePath, $updatedEnvContent);
    }
};
