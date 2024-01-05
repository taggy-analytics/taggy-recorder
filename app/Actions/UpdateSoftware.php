<?php

namespace App\Actions;

use App\Exceptions\MothershipException;
use App\Models\UserToken;
use App\Support\Mothership;
use App\Support\Recorder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateSoftware
{
    public function execute()
    {
        $mothership = Mothership::make();

        try {
            $newVersion = $mothership->checkForUpdateFile();
        }
        catch(MothershipException $exception) {
            return [
                'updated' => false,
                'version' => Recorder::make()->currentSoftwareVersion(),
                'message' => 'Error while connecting to mothership: ' . $exception->getMessage(),
            ];
        }

        if($newVersion) {
            $releasePath = base_path('../' . Str::replace([':', ' '], '-', now()->toDateTimeString()));
            $file = $newVersion['filename'];

            $zip = new \ZipArchive();
            $zip->open(Storage::path('releases/' . $file));
            $internalName = $zip->getNameIndex(0);
            $zip->extractTo(base_path('../'));
            $zip->close();

            File::moveDirectory(base_path('../' . $internalName), $releasePath);

            $releasePath = realpath($releasePath);

            Storage::delete('releases/' . $file);

            symlink(realpath($releasePath . '/../../storage'), $releasePath . '/storage');
            symlink(realpath($releasePath . '/../../.env'), $releasePath . '/.env');

            chdir($releasePath);
            Process::run('composer install');
            Process::run('php artisan migrate --force');

            // Do final checks before activating new release
            if(!File::exists($releasePath . '/vendor')) {
                throw new \Exception('Vendor directory does not exist.');
            }

            unlink($releasePath . '/../../current');
            symlink($releasePath, $releasePath . '/../../current');

            Process::run('php artisan cache:clear');
            Process::run('php artisan schedule:clear-cache');
            Process::run('php artisan storage:link');
            Process::run('php artisan horizon:terminate');
            Process::run('php artisan taggy:delete-old-releases');

            Storage::put(Recorder::CURRENT_SOFTWARE_VERSION_FILENAME, $newVersion['version']);

            info('Updated software to ' . $newVersion['version']);

            return [
                'updated' => true,
                'version' => $newVersion['version'],
                'message' => 'Recorder was updated to latest software (' . $newVersion['version'] . ').',
            ];
        }
        else {
            return [
                'updated' => false,
                'version' => Recorder::make()->currentSoftwareVersion(),
                'message' => 'Recorder is already running on latest software (' . Recorder::make()->currentSoftwareVersion() . ').',
            ];
        }
    }
}
