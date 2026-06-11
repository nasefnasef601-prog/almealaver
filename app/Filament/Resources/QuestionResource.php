<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationLabel = 'بنك الأسئلة';

    protected static ?string $pluralLabel = 'الأسئلة';

    protected static ?string $label = 'سؤال';

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
                Forms\Components\Select::make('section_id')
                    ->label('القسم')
                    ->relationship('section', 'name_ar')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('question_type')
                    ->label('النوع')
                    ->options(['mcq' => 'اختيار من متعدد', 'true_false' => 'صح/خطأ'])
                    ->required(),
                Forms\Components\Select::make('difficulty')
                    ->label('المستوى')
                    ->options(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب']),
                Forms\Components\Textarea::make('question_text_ar')
                    ->label('نص السؤال (عربي)')
                    ->required()
                    ->rows(3),
                Forms\Components\Textarea::make('question_text')
                    ->label('نص السؤال (إنجليزي)')
                    ->rows(3),
                Forms\Components\Repeater::make('options')
                    ->label('الخيارات')
                    ->schema([
                        Forms\Components\TextInput::make('text_ar')
                            ->label('النص (عربي)'),
                        Forms\Components\TextInput::make('text')
                            ->label('النص (إنجليزي)'),
                        Forms\Components\Toggle::make('is_correct')
                            ->label('الإجابة الصحيحة')
                            ->default(false),
                    ])
                    ->defaultItems(4)
                    ->maxItems(6)
                    ->addable(true)
                    ->deletable(true)
                    ->visible(fn (callable $get) => $get('question_type') === 'mcq'),
                Forms\Components\Select::make('correct_answer')
                    ->label('الإجابة الصحيحة')
                    ->options([
                        'true' => 'صح',
                        'false' => 'خطأ',
                    ])
                    ->visible(fn (callable $get) => $get('question_type') === 'true_false')
                    ->required(),
                Forms\Components\Textarea::make('explanation_ar')
                    ->label('الشرح (عربي)')
                    ->rows(2),
                Forms\Components\Textarea::make('explanation')
                    ->label('الشرح (إنجليزي)')
                    ->rows(2),
                Forms\Components\TextInput::make('points')
                    ->label('الدرجة')
                    ->numeric()
                    ->default(1),
                Forms\Components\Select::make('quiz_id')
                    ->label('الاختبار')
                    ->relationship('quiz', 'title_ar')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options(['active' => 'نشط', 'draft' => 'مسودة', 'archived' => 'مؤرشف'])
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question_text_ar')
                    ->label('السؤال')
                    ->limit(60)
                    ->searchable(),
                Tables\Columns\TextColumn::make('skill.name_ar')
                    ->label('المهارة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'mcq' ? 'اختيار من متعدد' : 'صح/خطأ'),
                Tables\Columns\TextColumn::make('difficulty')
                    ->label('المستوى')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'easy' => 'سهل',
                        'medium' => 'متوسط',
                        'hard' => 'صعب',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('points')
                    ->label('الدرجة'),
                Tables\Columns\TextColumn::make('quiz.title_ar')
                    ->label('الاختبار')
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('skill_id')
                    ->label('المهارة')
                    ->relationship('skill', 'name_ar')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'name_ar'),
                Tables\Filters\SelectFilter::make('section_id')
                    ->label('القسم')
                    ->relationship('section', 'name_ar'),
                Tables\Filters\SelectFilter::make('question_type')
                    ->label('النوع')
                    ->options(['mcq' => 'اختيار من متعدد', 'true_false' => 'صح/خطأ']),
                Tables\Filters\SelectFilter::make('difficulty')
                    ->label('المستوى')
                    ->options(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب']),
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(['active' => 'نشط', 'draft' => 'مسودة', 'archived' => 'مؤرشف']),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
                Actions\DeleteAction::make()->label('حذف'),
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
