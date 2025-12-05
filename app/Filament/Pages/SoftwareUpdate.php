<?php

namespace App\Filament\Pages;

use App\Jobs\UpdateSoftware;
use App\Support\Recorder;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use UnitEnum;

class SoftwareUpdate extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.pages.software-update';

    public $updateVersion;

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('gui.settings.heading');
    }

    public function mount()
    {
        $this->updateVersion = $this->getUpdateVersion();
    }

    protected function getViewData(): array
    {
        return [
            'currentRecorderVersion' => Recorder::make()->currentSoftwareVersion(),
        ];
    }

    public function updateAction(): Action
    {
        return Action::make('update')
            ->requiresConfirmation()
            ->modalDescription(__('gui.settings.software-update.are-you-sure'))
            ->action(function () {
                UpdateSoftware::putLock();
                UpdateSoftware::dispatch($this->updateVersion);

                Notification::make()
                    ->title(__('gui.settings.software-update.update-was-started', ['version' => $this->updateVersion['name']]))
                    ->success()
                    ->send();
            });
    }

    private function getUpdateVersion()
    {
        if (! Recorder::make()->connectedToInternet()) {
            return false;
        }

        $repo = 'https://api.github.com/repos/' . config('taggy-recorder.software.repository');
        if (config('taggy-recorder.software.update-channel') === 'prod') {
            $updateVersion = Http::get($repo . '/releases/tags/prod')->json();

            return [
                'name' => $updateVersion['name'],
                'url' => $updateVersion['tarball_url'],
            ];
        } else {
            $updateVersion = Http::get($repo . '/commits?sha=master')->json();
            $sha = Arr::get($updateVersion, '0.sha');

            return [
                'name' => $sha,
                'url' => 'https://github.com/' . config('taggy-recorder.software.repository') . '/archive/' . $sha . '.zip',
            ];
        }
    }
}
