<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Models\Lesson;
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

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-play-circle';

    protected static ?string $navigationLabel = 'الدروس';

    protected static ?string $pluralLabel = 'الدروس';

    protected static ?string $label = 'درس';

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
                Forms\Components\Select::make('module_id')
                    ->label('الموديول')
                    ->relationship('module', 'title_ar')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('content_type')
                    ->label('نوع المحتوى')
                    ->options([
                        'video' => 'فيديو',
                        'text' => 'نص',
                        'pdf' => 'PDF',
                        'youtube' => 'يوتيوب',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('content_url')
                    ->label('رابط المحتوى'),
                Forms\Components\Textarea::make('content_text')
                    ->label('النص')
                    ->rows(5),
                Forms\Components\TextInput::make('video_url')
                    ->label('رابط الفيديو'),
                Forms\Components\Select::make('video_provider')
                    ->label('مزود الفيديو')
                    ->options(['youtube' => 'يوتيوب', 'vimeo' => 'Vimeo', 'local' => 'مدمج']),
                Forms\Components\Textarea::make('description_ar')
                    ->label('الوصف (عربي)')
                    ->rows(3),
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
                Tables\Columns\TextColumn::make('module.title_ar')
                    ->label('الموديول'),
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
            ->filters([
                Tables\Filters\SelectFilter::make('content_type')
                    ->options([
                        'video' => 'فيديو',
                        'text' => 'نص',
                        'pdf' => 'PDF',
                        'youtube' => 'يوتيوب',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published'),
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
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
