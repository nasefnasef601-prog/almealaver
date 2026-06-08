<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use BackedEnum;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CourseModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static ?string $title = 'الموديولات';

    protected static string | BackedEnum | null $icon = 'heroicon-o-rectangle-stack';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title_ar')
                    ->label('العنوان (عربي)')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('العنوان (إنجليزي)'),
                Forms\Components\Textarea::make('description_ar')
                    ->label('الوصف (عربي)')
                    ->rows(3),
                Forms\Components\TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_free')
                    ->label('مجاني'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lessons_count')
                    ->label('الدروس')
                    ->counts('lessons'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب'),
                Tables\Columns\IconColumn::make('is_free')
                    ->label('مجاني')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة موديول'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }
}
