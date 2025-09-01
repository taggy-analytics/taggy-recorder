<?php

namespace App\Actions;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonTimeZone;
use Exception;
use Illuminate\Support\Facades\Log;

class SetSystemTime
{
    /**
     * Set the system time on Ubuntu OS
     *
     * @param  CarbonImmutable  $datetime  Carbon datetime instance
     * @param  string|null  $timezone  Optional timezone to apply (e.g., 'UTC', 'America/New_York', 'Europe/London')
     * @return array Result with success status and message
     *
     * @throws Exception
     */
    public function execute(CarbonImmutable $datetime, ?string $timezone = null): array
    {
        try {
            $carbonDateTime = $timezone ? $datetime->setTimezone($timezone) : $datetime;

            // Validate timezone if provided
            if ($timezone && ! $this->isValidTimezone($timezone)) {
                throw new Exception("Invalid timezone: {$timezone}");
            }

            $commands = [];

            // Set timezone if provided
            if ($timezone) {
                $commands[] = "sudo timedatectl set-timezone {$timezone}";
            }

            // Disable NTP synchronization temporarily to allow manual time setting
            $commands[] = 'sudo timedatectl set-ntp false';

            $formattedDateTime = $carbonDateTime->format('Y-m-d H:i:s');
            $commands[] = "sudo date -u -s '{$formattedDateTime}'";

            // Re-enable NTP
            $commands[] = 'sudo timedatectl set-ntp true';

            $results = [];

            foreach ($commands as $command) {
                $output = [];
                $returnCode = 0;

                exec($command.' 2>&1', $output, $returnCode);

                $results[] = [
                    'command' => $command,
                    'output' => implode("\n", $output),
                    'return_code' => $returnCode,
                    'success' => $returnCode === 0,
                ];

                if ($returnCode !== 0) {
                    $errorMessage = "Command failed: {$command}. Output: ".implode("\n", $output);
                    Log::error($errorMessage);
                    throw new Exception($errorMessage);
                }
            }

            // Verify the time was set correctly
            $currentTime = $this->getCurrentSystemTime();

            Log::info('System time successfully set', [
                'requested_datetime' => $carbonDateTime->toISOString(),
                'formatted_datetime' => $formattedDateTime,
                'timezone' => $timezone ?? $carbonDateTime->getTimezone()->getName(),
                'current_system_time' => $currentTime->toISOString(),
                'commands_executed' => array_column($results, 'command'),
            ]);

            return [
                'success' => true,
                'message' => "System time successfully set to: {$formattedDateTime}".($timezone ? " ({$timezone})" : ''),
                'requested_datetime' => $carbonDateTime->toISOString(),
                'formatted_datetime' => $formattedDateTime,
                'current_time' => $currentTime->toISOString(),
                'timezone' => $timezone ?? $carbonDateTime->getTimezone()->getName(),
                'commands_executed' => $results,
            ];

        } catch (Exception $e) {
            Log::error('Failed to set system time: '.$e->getMessage(), [
                'datetime' => $datetime->toISOString(),
                'timezone' => $timezone,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate timezone using Carbon
     */
    private function isValidTimezone(string $timezone): bool
    {
        try {
            new CarbonTimeZone($timezone);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get current system time as Carbon instance
     */
    private function getCurrentSystemTime(): Carbon
    {
        $output = [];
        exec('timedatectl show --property=LocalTime --value 2>/dev/null', $output);

        if (empty($output)) {
            exec('date "+%Y-%m-%d %H:%M:%S %Z"', $output);
        }

        try {
            return Carbon::parse($output[0] ?? 'now');
        } catch (Exception $e) {
            return Carbon::now();
        }
    }

    /**
     * Get system timezone information with Carbon formatting
     */
    public function getTimezoneInfo(): array
    {
        $output = [];
        exec('timedatectl status 2>/dev/null', $output);

        // Get current system timezone using Carbon
        $currentSystemTime = $this->getCurrentSystemTime();

        return [
            'current_time' => $currentSystemTime->toISOString(),
            'current_timezone' => $currentSystemTime->getTimezone()->getName(),
            'formatted_time' => $currentSystemTime->format('Y-m-d H:i:s T'),
            'human_readable' => $currentSystemTime->toDayDateTimeString(),
            'timedatectl_output' => implode("\n", $output),
            'available_timezones_sample' => $this->getAvailableTimezones(),
        ];
    }

    /**
     * Get available timezones
     */
    private function getAvailableTimezones(): array
    {
        // Get common timezones using Carbon
        $commonTimezones = [
            'UTC',
            'America/New_York',
            'America/Chicago',
            'America/Los_Angeles',
            'Europe/London',
            'Europe/Berlin',
            'Europe/Paris',
            'Asia/Tokyo',
            'Asia/Shanghai',
            'Australia/Sydney',
        ];

        // Also get system available timezones
        $output = [];
        exec('timedatectl list-timezones 2>/dev/null | head -10', $output);

        return [
            'common' => $commonTimezones,
            'system_sample' => $output,
        ];
    }

    /**
     * Reset to NTP synchronization with Carbon logging
     */
    public function enableNTPSync(): array
    {
        try {
            $beforeTime = CarbonImmutable::now();

            $output = [];
            $returnCode = 0;

            exec('sudo timedatectl set-ntp true 2>&1', $output, $returnCode);

            if ($returnCode === 0) {
                $afterTime = $this->getCurrentSystemTime();

                Log::info('NTP synchronization enabled', [
                    'before_enable' => $beforeTime->toISOString(),
                    'after_enable' => $afterTime->toISOString(),
                    'time_difference' => $beforeTime->diffInSeconds($afterTime).' seconds',
                ]);

                return [
                    'success' => true,
                    'message' => 'NTP synchronization enabled',
                    'before_time' => $beforeTime->toISOString(),
                    'current_time' => $afterTime->toISOString(),
                    'output' => implode("\n", $output),
                ];
            } else {
                throw new Exception('Failed to enable NTP: '.implode("\n", $output));
            }

        } catch (Exception $e) {
            Log::error('Failed to enable NTP: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Utility method to get relative time descriptions
     */
    public function getRelativeTimeInfo(CarbonImmutable $targetTime): array
    {
        $now = CarbonImmutable::now();

        return [
            'target_time' => $targetTime->toISOString(),
            'current_time' => $now->toISOString(),
            'is_future' => $targetTime->isFuture(),
            'is_past' => $targetTime->isPast(),
            'human_diff' => $targetTime->diffForHumans($now),
            'exact_diff' => [
                'years' => $now->diffInYears($targetTime),
                'months' => $now->diffInMonths($targetTime),
                'days' => $now->diffInDays($targetTime),
                'hours' => $now->diffInHours($targetTime),
                'minutes' => $now->diffInMinutes($targetTime),
                'seconds' => $now->diffInSeconds($targetTime),
            ],
        ];
    }
}
