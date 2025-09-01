<?php

namespace App\Actions\Mothership;

use App\Models\UserToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;

class ManageWebsocketsConnection
{
    public function execute()
    {
        $entities = UserToken::perEntity()
            ->whereNull('last_rejected_at');

        foreach ($entities as $entityId => $userTokens) {
            if (! $this->entityHasRunningProcess($entityId)) {
                foreach ($userTokens as $userToken) {
                    $command = "node echo.js {$entityId} '{$userToken->token}' > /dev/null 2>&1 &";
                    exec($command);

                    sleep(2);
                    if ($this->entityHasRunningProcess($entityId)) {
                        $userToken->update(['last_successfully_used_at' => now()]);

                        continue 2;
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
            ->filter(fn ($process) => preg_match('/node echo\.js \d+ \w+/', $process['command']))
            ->map(fn ($process) => (int) explode(' ', $process['command'])[2]);
    }

    private function getProcesses()
    {
        $output = explode(PHP_EOL, Process::run('ps aux |grep "node echo.js"')->output());

        $processes = [];

        foreach ($output as $index => $line) {
            // Parse each line into columns
            preg_match_all('/\S+/', $line, $columns);

            if (! empty($columns[0])) {
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
                    'command' => implode(' ', array_slice($columns[0], 10)),
                ];
            }
        }

        return $processes;
    }
}
