<?php

namespace App\Filament\Resources\B2BPackageResource\Pages;

use App\Filament\Resources\B2BPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListB2BPackages extends ListRecords
{
    protected static string $resource = B2BPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
