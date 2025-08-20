<?php

namespace App\Actions;

use App\Support\Recorder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateSoftwareExecute
{
    public function execute($version, $filename)
    {
        return cache()->lock('lock-update-software-execute', 300)->block(300, function () use ($version, $filename) {
            app(CalculateLed::class)->execute();

            $releasePath = base_path('../' . Str::replace([':', ' '], '-', now()->toDateTimeString()));

            $zip = new \ZipArchive();
            $zip->open(Storage::path('releases/' . $filename));
            $internalName = $zip->getNameIndex(0);
            $zip->extractTo(base_path('../'));
            $zip->close();

            File::moveDirectory(base_path('../' . $internalName), $releasePath);

            $releasePath = realpath($releasePath);

            Storage::delete('releases/' . $filename);

            symlink(realpath($releasePath . '/../../storage'), $releasePath . '/storage');
            symlink(realpath($releasePath . '/../../.env'), $releasePath . '/.env');

            chdir($releasePath);
            Process::timeout(180)->run('npm install');
            Process::timeout(180)->run('npm run build');
            Process::timeout(120)
                ->env(['HOME' => '/home/taggy'])
                ->run('/usr/local/bin/composer install');
            Process::run('sudo php artisan migrate --force');

            if(!Process::run('php artisan taggy:check-software ' . $releasePath)->successful()) {
                return [
                    'updated' => false,
                    'version' => Recorder::make()->currentSoftwareVersion(),
                    'message' => 'Software validation failed.',
                ];
            }

            unlink($releasePath . '/../../current');

            symlink($releasePath, $releasePath . '/../../current');

            Process::run('php artisan cache:clear');
            Process::run('php artisan schedule:clear-cache');
            Process::run('php artisan storage:link');
            Process::run('php artisan horizon:terminate');
            // ToDo: kill running watch-segments and upload-livestream processes
            Process::run('php artisan taggy:delete-old-releases');

            Storage::put(Recorder::CURRENT_SOFTWARE_VERSION_FILENAME, $version);

            info('Updated software to ' . $version);

            app(CalculateLed::class)->execute();

            return [
                'updated' => true,
                'version' => $version,
                'message' => 'Recorder was updated to latest software (' . $version . ').',
            ];
        });
    }
}
