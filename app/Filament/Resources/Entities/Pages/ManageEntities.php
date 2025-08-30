<?php

namespace App\Filament\Resources\Entities\Pages;

use App\Filament\Resources\Entities\EntityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEntities extends ManageRecords
{
    protected static string $resource = EntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
