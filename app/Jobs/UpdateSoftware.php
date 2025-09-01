<?php

namespace App\Jobs;

use App\Actions\UpdateSoftwareExecute;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class UpdateSoftware implements ShouldQueue
{
    use Queueable;

    private const UpdateLockFileName = 'software-update.lock';

    public function __construct(
        private $updateVersion,
    ) {}

    public function handle(): void
    {
        self::putLock();

        $filename = $this->updateVersion['name'].'.zip';

        Storage::put('releases/'.$filename, file_get_contents($this->updateVersion['url']));

        $updateResult = app(UpdateSoftwareExecute::class)
            ->execute($this->updateVersion['name'], $filename);

        info($updateResult);

        self::removeLock();
    }

    public static function isRunning(): bool
    {
        return Storage::exists(self::UpdateLockFileName);
    }

    public static function putLock()
    {
        Storage::put(self::UpdateLockFileName, 'running...');
    }

    public static function removeLock()
    {
        Storage::delete(self::UpdateLockFileName);
    }
}
