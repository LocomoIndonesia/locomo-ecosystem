<?php

namespace App\Filament\Resources\EcosystemServiceResource\Pages;

use App\Filament\Resources\EcosystemServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEcosystemServices extends ListRecords
{
    protected static string $resource = EcosystemServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
