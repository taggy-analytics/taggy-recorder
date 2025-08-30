<?php

namespace App\Filament\Resources\Recordings\Pages;

use App\Filament\Resources\Recordings\RecordingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRecordings extends ManageRecords
{
    protected static string $resource = RecordingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
