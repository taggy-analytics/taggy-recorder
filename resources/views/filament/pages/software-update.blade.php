<x-filament-panels::page>
    <div>
        <div>
            @lang('gui.settings.software-update.current-version'): <strong>{{ $currentRecorderVersion }}</strong>
        </div>
        @if(!$updateVersion)
            <div class="text-sm italic mt-2">
                @lang('gui.settings.software-update.offline')
            </div>
        @elseif(\App\Support\Recorder::make()->isUpdatingFirmware())
            <div wire:poll class="flex gap-2 items-center">
                <x-filament::loading-indicator />
                @lang('gui.settings.software-update.update-is-running')
            </div>
        @elseif($currentRecorderVersion === $updateVersion['name'])
            <div>
                @lang('gui.settings.software-update.software-is-current')
            </div>
        @else
            <div>
                <div>
                    @lang('gui.settings.software-update.new-software-available', ['version' => $updateVersion['name']]))
                </div>
                <div class="mt-4">
                    {{ $this->updateAction }}
                </div>
                <x-filament-actions::modals />
            </div>
        @endif
    </div>
</x-filament-panels::page>
