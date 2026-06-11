<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'الدروس';

    protected static string | BackedEnum | null $icon = 'heroicon-o-play-circle';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title_ar')
                    ->label('العنوان (عربي)')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('العنوان (إنجليزي)'),
                Forms\Components\Select::make('content_type')
                    ->label('نوع المحتوى')
                    ->options([
                        'video' => 'فيديو',
                        'text' => 'نص',
                        'pdf' => 'PDF',
                        'youtube' => 'يوتيوب',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('content_text_ar')
                    ->label('النص (عربي)')
                    ->rows(5),
                Forms\Components\TextInput::make('video_url')
                    ->label('رابط الفيديو'),
                Forms\Components\TextInput::make('duration_minutes')
                    ->label('المدة (دقائق)')
                    ->numeric(),
                Forms\Components\TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_free')
                    ->label('مجاني'),
                Forms\Components\Toggle::make('is_published')
                    ->label('منشور'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('content_type')
                    ->label('النوع')
                    ->badge(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('المدة')
                    ->suffix(' د'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Actions\CreateAction::make()->label('إضافة درس'),
            ])
            ->actions([
                Actions\EditAction::make()->label('تعديل'),
                Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }
}
