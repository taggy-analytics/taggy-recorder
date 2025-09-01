<?php

namespace App\Actions;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class EnsureAppKeyIsSet
{
    public function execute()
    {
        if (! Str::contains(DotenvEditor::getValue('APP_KEY'), 'base64')) {
            Artisan::call('key:generate');

            return false;
        }

        return true;
    }
}
