<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'الأقسام';

    protected static ?string $pluralLabel = 'الأقسام';

    protected static ?string $label = 'قسم';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name_ar')
                    ->required(),
                Forms\Components\TextInput::make('name_ar')
                    ->label('الاسم (عربي)')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('الاسم (إنجليزي)'),
                Forms\Components\TextInput::make('slug')
                    ->label('الرابط المختصر')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description_ar')
                    ->label('الوصف (عربي)'),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف (إنجليزي)'),
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.name_ar')
                    ->label('المادة'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('skills_count')
                    ->label('المهارات')
                    ->counts('skills'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name_ar'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}
