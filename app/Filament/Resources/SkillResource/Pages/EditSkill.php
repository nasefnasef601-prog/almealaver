<?php

namespace App\Filament\Resources\SkillResource\Pages;

use App\Filament\Resources\SkillResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Widgets\WidgetConfiguration;

class EditSkill extends EditRecord
{
    protected static string $resource = SkillResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            new \Filament\Widgets\WidgetConfiguration(
                \App\Filament\Resources\SkillResource\Widgets\SkillStatsOverview::class,
                ['recordId' => $this->record?->id],
            ),
        ];
    }
}
