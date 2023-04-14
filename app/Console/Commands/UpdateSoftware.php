<?php

namespace App\Console\Commands;

use App\Support\Mothership;
use App\Support\Recorder;
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
        if($file = Mothership::make()->checkForUpdateFile()) {
            $releasePath = base_path('../' . Str::replace(':', '-', now()->toDateTimeString()));

            $zip = new \ZipArchive();
            $zip->open(Storage::path('releases/' . $file));
            $internalName = $zip->getNameIndex(0);
            $zip->extractTo(base_path('../'));
            $zip->close();

            File::moveDirectory(base_path('../' . $internalName), $releasePath);

            Storage::delete('releases/' . $file);

            symlink($releasePath . '/../storage', $releasePath . '/storage');
            symlink($releasePath . '/../.env', $releasePath . '/.env');

            chdir($releasePath);
            Process::run('composer install');
            Process::run('php artisan migrate --force');

            unlink($releasePath . '/../current');
            symlink($releasePath, $releasePath . '/../../current');

            Process::run('php artisan cache:clear');
            Process::run('php artisan horizon:terminate');
            Process::run('php artisan taggy:delete-old-releases');
        }

        return 0;
    }
}
