<?php

namespace App\Filament\Pages;

use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class QuizResults extends Page
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'نتائج الاختبارات';

    protected static ?string $title = 'نتائج الاختبارات';

    protected string $view = 'filament.pages.quiz-results';

    public function table(Table $table): Table
    {
        return $table
            ->query(QuizResult::with('user', 'quiz'))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quiz.title_ar')
                    ->label('الاختبار')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_percentage')
                    ->label('النتيجة')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->color(fn ($record) => $record->passed ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\IconColumn::make('passed')
                    ->label('النتيجة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('correct_count')
                    ->label('الصحيح')
                    ->formatStateUsing(fn ($record) => "{$record->correct_count}/{$record->total_questions}")
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->label('الاختبار')
                    ->options(Quiz::where('is_published', true)->pluck('title_ar', 'id')),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('الطالب')
                    ->options(User::where('role', 'student')->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('passed')
                    ->label('النتيجة')
                    ->trueLabel('ناجح')
                    ->falseLabel('راسب')
                    ->nullable(),
            ])
            ->bulkActions([]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }
}
