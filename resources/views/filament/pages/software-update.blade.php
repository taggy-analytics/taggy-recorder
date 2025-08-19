<x-filament-panels::page>
    <div>
        <div>
            {{ __('gui.settings.software-update.current-version') }}: <strong>{{ $currentRecorderVersion }}</strong>
        </div>
        @if(\App\Support\Recorder::make()->isUpdatingFirmware())
            <div wire:poll class="flex gap-2 items-center">
                <x-filament::loading-indicator />
                {{ __('gui.settings.software-update.update-is-running') }}
            </div>
        @elseif($currentRecorderVersion === $updateVersion['name'])
            <div>
                {{ __('gui.settings.software-update.software-is-current') }}
            </div>
        @else
            <div>
                <div>
                    {!! __('gui.settings.software-update.new-software-available', ['version' => $updateVersion['name']]) !!}<br/>
                </div>
                <div class="mt-4">
                    {{ $this->updateAction }}
                </div>
                <x-filament-actions::modals />
            </div>
        @endif
    </div>
</x-filament-panels::page>
