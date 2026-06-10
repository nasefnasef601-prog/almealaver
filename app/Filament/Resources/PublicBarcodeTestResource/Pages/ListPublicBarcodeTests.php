<?php

namespace App\Filament\Resources\PublicBarcodeTestResource\Pages;

use App\Filament\Resources\PublicBarcodeTestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublicBarcodeTests extends ListRecords
{
    protected static string $resource = PublicBarcodeTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
