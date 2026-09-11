<?php

namespace App\Filament\Resources\ServiceLicenseResource\Pages;

use App\Filament\Resources\ServiceLicenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceLicenses extends ListRecords
{
    protected static string $resource = ServiceLicenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
