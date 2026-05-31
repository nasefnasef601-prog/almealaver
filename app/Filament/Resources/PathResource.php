<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PathResource\Pages;
use App\Models\Path;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class PathResource extends Resource
{
    protected static ?string $model = Path::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'المسارات';

    protected static ?string $pluralLabel = 'المسارات';

    protected static ?string $label = 'مسار';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
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
                Forms\Components\TextInput::make('icon')
                    ->label('الأيقونة'),
                Forms\Components\ColorPicker::make('color')
                    ->label('اللون'),
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
                Tables\Columns\TextColumn::make('slug')
                    ->label('الرابط المختصر'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('subjects_count')
                    ->label('عدد المواد')
                    ->counts('subjects'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\Filter::make('is_active')
                    ->label('نشط فقط')
                    ->query(fn ($query) => $query->where('is_active', true)),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaths::route('/'),
            'create' => Pages\CreatePath::route('/create'),
            'edit' => Pages\EditPath::route('/{record}/edit'),
        ];
    }
}
