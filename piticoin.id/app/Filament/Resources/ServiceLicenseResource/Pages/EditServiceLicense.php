<?php

namespace App\Filament\Resources\ServiceLicenseResource\Pages;

use App\Filament\Resources\ServiceLicenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceLicense extends EditRecord
{
    protected static string $resource = ServiceLicenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
