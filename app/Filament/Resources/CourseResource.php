<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
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

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'الكورسات';

    protected static ?string $pluralLabel = 'الكورسات';

    protected static ?string $label = 'كورس';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('skill_id')
                    ->label('المهارة')
                    ->relationship('skill', 'name_ar')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name_ar')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('title_ar')
                    ->label('العنوان (عربي)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->label('العنوان (إنجليزي)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->label('الرابط المختصر')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\RichEditor::make('description_ar')
                    ->label('الوصف (عربي)'),
                Forms\Components\RichEditor::make('description')
                    ->label('الوصف (إنجليزي)'),
                Forms\Components\TextInput::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\Toggle::make('is_free')
                    ->label('مجاني'),
                Forms\Components\Select::make('assigned_teacher_id')
                    ->label('المدرس المسؤول')
                    ->relationship('assignedTeacher', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Toggle::make('is_published')
                    ->label('منشور'),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'pending' => 'قيد المراجعة',
                        'approved' => 'معتمد',
                        'rejected' => 'مرفوض',
                        'archived' => 'مؤرشف',
                    ])
                    ->default('draft'),
                Forms\Components\Select::make('difficulty_level')
                    ->label('مستوى الصعوبة')
                    ->options([
                        'beginner' => 'مبتدئ',
                        'intermediate' => 'متوسط',
                        'advanced' => 'متقدم',
                        'all' => 'جميع المستويات',
                    ]),
                Forms\Components\TextInput::make('duration_minutes')
                    ->label('المدة (دقائق)')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\Toggle::make('has_certificate')
                    ->label('شهادة إتمام'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('skill.name_ar')
                    ->label('المهارة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('SAR'),
                Tables\Columns\IconColumn::make('is_free')
                    ->label('مجاني')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_by')
                    ->label('المدرس')
                    ->formatStateUsing(fn ($state) => \App\Models\User::find($state)?->name ?? '—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('skill_id')
                    ->label('المهارة')
                    ->relationship('skill', 'name_ar')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name_ar'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'مسودة',
                        'pending' => 'قيد المراجعة',
                        'approved' => 'معتمد',
                        'rejected' => 'مرفوض',
                    ]),
                Tables\Filters\SelectFilter::make('is_free')
                    ->label('النوع')
                    ->options([
                        '1' => 'مجاني',
                        '0' => 'مدفوع',
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
            \App\Filament\Resources\CourseResource\RelationManagers\CourseModulesRelationManager::class,
            \App\Filament\Resources\CourseResource\RelationManagers\LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
