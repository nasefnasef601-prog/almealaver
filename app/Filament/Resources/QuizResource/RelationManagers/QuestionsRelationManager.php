<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'الأسئلة';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('question_type')
                    ->label('النوع')
                    ->options(['mcq' => 'اختيار من متعدد', 'true_false' => 'صح/خطأ'])
                    ->required(),
                Forms\Components\Textarea::make('question_text_ar')
                    ->label('نص السؤال (عربي)')
                    ->required()
                    ->rows(3),
                Forms\Components\KeyValue::make('options')
                    ->label('الخيارات')
                    ->keyLabel('مفتاح')
                    ->valueLabel('القيمة'),
                Forms\Components\TextInput::make('correct_answer')
                    ->label('الإجابة الصحيحة')
                    ->required(),
                Forms\Components\Textarea::make('explanation_ar')
                    ->label('الشرح (عربي)')
                    ->rows(2),
                Forms\Components\TextInput::make('points')
                    ->label('الدرجة')
                    ->numeric()
                    ->default(1),
                Forms\Components\Select::make('difficulty')
                    ->label('المستوى')
                    ->options(['easy' => 'سهل', 'medium' => 'متوسط', 'hard' => 'صعب']),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question_text_ar')
                    ->label('السؤال')
                    ->limit(50),
                Tables\Columns\TextColumn::make('question_type')
                    ->label('النوع'),
                Tables\Columns\TextColumn::make('difficulty')
                    ->label('المستوى'),
                Tables\Columns\TextColumn::make('points')
                    ->label('الدرجة'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
