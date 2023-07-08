<?php

namespace App\Console\Commands;

use App\Exceptions\RecorderNotAssociatedException;
use App\Support\Mothership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateSoftware extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taggy:update-software';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update software';

    /**
     * ra
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mothership = Mothership::make();

        try {
            $newVersion = $mothership->checkForUpdateFile();
        }
        catch(RecorderNotAssociatedException $exception) {
            $this->error('Recorder is not associated with any organization.');
            return 1;
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

            // As long as we are not API exclusively
            Process::run('npm install');
            Process::run('npm run build');

            unlink($releasePath . '/../../current');
            symlink($releasePath, $releasePath . '/../../current');

            Process::run('php artisan cache:clear');
            Process::run('php artisan storage:link');
            Process::run('php artisan horizon:terminate');
            Process::run('php artisan taggy:delete-old-releases');

            Storage::put(Mothership::CURRENT_SOFTWARE_VERSION_FILENAME, $newVersion['version']);

            $this->info('Recorder was updated to latest software (' . $newVersion['version'] . ').');
        }
        else {
            $this->info('Recorder is already running on latest software (' . $mothership->currentSoftwareVersion() . ').');
        }

        return 0;
    }
}
