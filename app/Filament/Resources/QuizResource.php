<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Models\Quiz;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'الاختبارات';

    protected static ?string $pluralLabel = 'الاختبارات';

    protected static ?string $label = 'اختبار';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('العنوان (إنجليزي)')
                    ->required(),
                Forms\Components\TextInput::make('title_ar')
                    ->label('العنوان (عربي)'),
                Forms\Components\Select::make('quiz_type')
                    ->label('النوع')
                    ->options(['quiz' => 'اختبار', 'training' => 'تدريب', 'mock_exam' => 'اختبار محاكي'])
                    ->required(),
                Forms\Components\Select::make('difficulty')
                    ->label('المستوى')
                    ->options(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب']),
                Forms\Components\Select::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name_ar')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('section_id')
                    ->label('القسم')
                    ->relationship('section', 'name_ar')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('course_id')
                    ->label('الكورس')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('time_limit')
                    ->label('الوقت (دقائق)')
                    ->numeric()
                    ->suffix('دقيقة'),
                Forms\Components\TextInput::make('passing_score')
                    ->label('حد النجاح %')
                    ->numeric()
                    ->suffix('%')
                    ->default(50),
                Forms\Components\TextInput::make('max_attempts')
                    ->label('عدد المحاولات')
                    ->numeric()
                    ->placeholder('غير محدود'),
                Forms\Components\Toggle::make('randomize_questions')
                    ->label('ترتيب عشوائي للأسئلة'),
                Forms\Components\Toggle::make('show_answers')
                    ->label('إظهار الإجابات'),
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
                Tables\Columns\TextColumn::make('subject.name_ar')
                    ->label('المادة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('section.name_ar')
                    ->label('القسم'),
                Tables\Columns\TextColumn::make('quiz_type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'quiz' => 'success',
                        'training' => 'warning',
                        'mock_exam' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('difficulty')
                    ->label('المستوى'),
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('الأسئلة')
                    ->counts('questions'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name_ar'),
                Tables\Filters\SelectFilter::make('section_id')
                    ->label('القسم')
                    ->relationship('section', 'name_ar'),
                Tables\Filters\SelectFilter::make('quiz_type')
                    ->options([
                        'quiz' => 'اختبار',
                        'training' => 'تدريب',
                        'mock_exam' => 'اختبار محاكي',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published'),
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

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\QuizResource\RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
