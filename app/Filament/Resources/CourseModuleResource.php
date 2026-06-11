<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseModuleResource\Pages;
use App\Models\CourseModule;
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

class CourseModuleResource extends Resource
{
    protected static ?string $model = CourseModule::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'الموديولات';

    protected static ?string $pluralLabel = 'الموديولات';

    protected static ?string $label = 'موديول';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title_ar')
                    ->label('العنوان (عربي)')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('العنوان (إنجليزي)'),
                Forms\Components\Select::make('course_id')
                    ->label('الكورس')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.title_ar')
                    ->label('الكورس')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lessons_count')
                    ->label('الدروس')
                    ->counts('lessons'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب'),
            ])
            ->defaultSort('sort_order')
            ->filters([])
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
            'index' => Pages\ListCourseModules::route('/'),
            'create' => Pages\CreateCourseModule::route('/create'),
            'edit' => Pages\EditCourseModule::route('/{record}/edit'),
        ];
    }
}
