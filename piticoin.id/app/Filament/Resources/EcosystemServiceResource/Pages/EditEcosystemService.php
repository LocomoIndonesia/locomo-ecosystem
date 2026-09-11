<?php

namespace App\Filament\Resources\EcosystemServiceResource\Pages;

use App\Filament\Resources\EcosystemServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEcosystemService extends EditRecord
{
    protected static string $resource = EcosystemServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
