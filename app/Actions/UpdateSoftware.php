<?php

namespace App\Actions;

use App\Support\Mothership;
use App\Support\Recorder;

class UpdateSoftware
{
    public function execute()
    {
        return cache()->lock('lock-update-software', 300)->block(300, function () {
            app(CalculateLed::class)->execute();

            $mothership = Mothership::make();

            try {
                $newVersion = $mothership->checkForUpdateFile();
            } catch (\Exception $exception) {
                return [
                    'updated' => false,
                    'version' => Recorder::make()->currentSoftwareVersion(),
                    'message' => 'Error while connecting to mothership: '.$exception->getMessage(),
                ];
            }

            if ($newVersion) {
                return app(UpdateSoftwareExecute::class)
                    ->execute($newVersion['version'], $newVersion['filename']);
            } else {
                return [
                    'updated' => false,
                    'version' => Recorder::make()->currentSoftwareVersion(),
                    'message' => 'Recorder is already running on latest software ('.Recorder::make()->currentSoftwareVersion().').',
                ];
            }
        });
    }
}
