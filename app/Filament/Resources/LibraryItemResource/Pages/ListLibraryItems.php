<?php

namespace App\Filament\Resources\LibraryItemResource\Pages;

use App\Filament\Resources\LibraryItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLibraryItems extends ListRecords
{
    protected static string $resource = LibraryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
