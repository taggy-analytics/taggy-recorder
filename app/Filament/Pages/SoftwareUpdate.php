<?php

namespace App\Filament\Pages;

use App\Support\Recorder;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use UnitEnum;

class SoftwareUpdate extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.pages.software-update';

    public $prodVersion;

    /**
     * @return string|UnitEnum|null
     */
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('gui.settings.heading');
    }

    public function mount()
    {
        $prodVersion = Http::get(
            "https://api.github.com/repos/taggy-analytics/taggy-recorder/releases/tags/prod"
        )->json();

        $this->prodVersion = Arr::only($prodVersion, ['name', 'tarball_url']);
    }

    protected function getViewData(): array
    {
        return [
            'currentRecorderVersion' => Recorder::make()->currentSoftwareVersion(),
        ];
    }

    public function updateAction() : Action
    {
        return Action::make('update')
            ->requiresConfirmation();
    }
}
