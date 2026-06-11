<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'المواد';

    protected static ?string $pluralLabel = 'المواد';

    protected static ?string $label = 'مادة';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('path_id')
                    ->label('المسار')
                    ->relationship('path', 'name_ar')
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
                Tables\Columns\TextColumn::make('path.name_ar')
                    ->label('المسار'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sections_count')
                    ->label('الأقسام')
                    ->counts('sections'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('path_id')
                    ->label('المسار')
                    ->relationship('path', 'name_ar'),
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
