<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'الأسئلة الشائعة';
    protected static ?string $pluralLabel = 'الأسئلة الشائعة';
    protected static ?string $label = 'سؤال';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('question_ar')->label('السؤال (عربي)')->required(),
            Forms\Components\TextInput::make('question')->label('السؤال (English)'),
            Forms\Components\RichEditor::make('answer_ar')->label('الإجابة (عربي)')->required(),
            Forms\Components\RichEditor::make('answer')->label('الإجابة (English)'),
            Forms\Components\TextInput::make('category')->label('التصنيف'),
            Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric(),
            Forms\Components\Toggle::make('is_published')->label('منشور'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question_ar')->label('السؤال')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('category')->label('التصنيف')->badge(),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListFaqs::route('/')];
    }
}
