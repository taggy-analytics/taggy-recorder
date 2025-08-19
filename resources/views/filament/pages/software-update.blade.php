<x-filament-panels::page>
    <div>
        {{ __('gui.settings.software-update.current-version') }}: <strong>{{ $currentRecorderVersion }}</strong>
    </div>
    @if($currentRecorderVersion === $prodVersion['name'])
        <div>
            {{ __('gui.settings.software-update.software-is-current') }}
        </div>
    @else
        <div>
            <div>
                {!! __('gui.settings.software-update.new-software-available', ['version' => $prodVersion['name']]) !!}<br/>
            </div>
            <div class="mt-4"   >
            {{ $this->updateAction }}
            </div>
            <x-filament-actions::modals />
        </div>
    @endif
</x-filament-panels::page>
