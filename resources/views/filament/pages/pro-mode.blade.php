<x-filament-panels::page>
    @if($switchedToProMode)
        @lang('gui.settings.pro-mode.switched-to-pro-mode')
    @else
        <div>
            @lang('gui.settings.pro-mode.description-1')
        </div>
        <div>
            @lang('gui.settings.pro-mode.description-2')
        </div>
        <div>
            @lang('gui.settings.pro-mode.description-3')
        </div>

        <div>
            {{ $this->switchToProModeAction }}
        </div>
    @endif
</x-filament-panels::page>
