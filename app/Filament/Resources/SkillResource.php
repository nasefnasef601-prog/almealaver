<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SkillResource\Pages;
use App\Models\Skill;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class SkillResource extends Resource
{
    protected static ?string $model = Skill::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationLabel = 'المهارات';

    protected static ?string $pluralLabel = 'المهارات';

    protected static ?string $label = 'مهارة';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('section_id')
                    ->label('القسم')
                    ->relationship('section', 'name_ar'),
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
                Forms\Components\Select::make('skill_category')
                    ->label('تصنيف المهارة')
                    ->options([
                        'knowledge' => 'معرفة',
                        'application' => 'تطبيق',
                        'reasoning' => 'استدلال',
                    ])
                    ->default('knowledge'),
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
                Tables\Columns\TextColumn::make('section.name_ar')
                    ->label('القسم'),
                Tables\Columns\TextColumn::make('skill_category')
                    ->label('التصنيف')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'knowledge' => 'معرفة',
                        'application' => 'تطبيق',
                        'reasoning' => 'استدلال',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                Tables\Columns\TextColumn::make('courses_count')
                    ->label('الكورسات')
                    ->counts('courses'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('section_id')
                    ->label('القسم')
                    ->relationship('section', 'name_ar'),
                Tables\Filters\SelectFilter::make('skill_category')
                    ->label('التصنيف')
                    ->options([
                        'knowledge' => 'معرفة',
                        'application' => 'تطبيق',
                        'reasoning' => 'استدلال',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\SkillResource\RelationManagers\CoursesRelationManager::class,
            \App\Filament\Resources\SkillResource\RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSkills::route('/'),
            'create' => Pages\CreateSkill::route('/create'),
            'edit' => Pages\EditSkill::route('/{record}/edit'),
        ];
    }
}
