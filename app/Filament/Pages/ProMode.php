<?php

namespace App\Filament\Pages;

use App\Support\Recorder;
use Filament\Actions\Action;
use Filament\Pages\Page;
use UnitEnum;

class ProMode extends Page
{
    protected string $view = 'filament.pages.pro-mode';

    protected static ?int $navigationSort = 999999;

    public bool $switchedToProMode = false;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('gui.settings.heading');
    }

    public function switchToProModeAction() : Action
    {
        return Action::make('switchToProMode')
            ->requiresConfirmation()
            ->modalDescription(__('gui.settings.software-update.are-you-sure'))
            ->action(function () {
                $this->switchedToProMode = true;
                dispatch(fn() => Recorder::make()->activateProMode())->delay(now()->addSeconds(2));
            });
    }
}
