<?php

namespace App\Actions\Mothership;

use App\Enums\RecordingFileStatus;
use App\Enums\RecordingStatus;
use App\Models\RecorderLog;
use App\Models\Recording;
use App\Models\RecordingFile;
use App\Models\UserToken;
use App\Support\Mothership;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class ManageWebsocketsConnection
{
    public function execute()
    {
        $entities = UserToken::perEntity()
            ->whereNull('last_rejected_at');

        foreach($entities as $entityId => $userTokens) {
            // dump($entityId, $this->entityHasRunningProcess($entityId), $this->entityHasRunningProcess($entityId));
            if(!$this->entityHasRunningProcess($entityId)) {
                foreach ($userTokens as $userToken) {
                    $command = "node echo.js {$entityId} '{$userToken->token}' > /dev/null 2>&1 &";
                    exec($command);

                    sleep(2);
                    if ($this->entityHasRunningProcess($entityId)) {
                        $userToken->update(['last_successfully_used_at' => now()]);
                        continue(2);
                    } else {
                        $userToken->update(['last_rejected_at' => now()]);
                    }
                }
            }
        }
    }

    private function entityHasRunningProcess($entityId)
    {
        return $this
            ->entitiesWithRunningProcess()
            ->contains($entityId);
    }

    private function entitiesWithRunningProcess(): Collection
    {
        return collect($this->getProcesses())
            ->filter(fn($process) => Str::contains($process['command'], 'node echo.js'))
            ->map(fn($process) => explode(' ', $process['command'])[2]);
    }

    private function getProcesses()
    {
        $output = [];
        exec('ps aux |grep echo', $output);

        $processes = [];

        foreach ($output as $index => $line) {
            if ($index === 0) {
                // Skip the header row
                continue;
            }

            // Parse each line into columns
            preg_match_all('/\S+/', $line, $columns);

            if (!empty($columns[0])) {
                $processes[] = [
                    'user' => $columns[0][0],
                    'pid' => $columns[0][1],
                    'cpu' => $columns[0][2],
                    'mem' => $columns[0][3],
                    'vsz' => $columns[0][4],
                    'rss' => $columns[0][5],
                    'tty' => $columns[0][6],
                    'stat' => $columns[0][7],
                    'start' => $columns[0][8],
                    'time' => $columns[0][9],
                    'command' => implode(' ', array_slice($columns[0], 10))
                ];
            }
        }

        return $processes;
    }
}
