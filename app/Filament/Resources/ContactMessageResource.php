<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'رسائل التواصل';
    protected static ?string $pluralLabel = 'رسائل التواصل';
    protected static ?string $label = 'رسالة';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->label('الاسم')->disabled(),
            Forms\Components\TextInput::make('email')->label('البريد')->disabled(),
            Forms\Components\TextInput::make('subject')->label('الموضوع')->disabled(),
            Forms\Components\Textarea::make('message')->label('الرسالة')->disabled()->rows(6),
            Forms\Components\Toggle::make('is_read')->label('مقروءة'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('البريد')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('الموضوع')->limit(40),
                Tables\Columns\IconColumn::make('is_read')->label('مقروءة')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('markRead')
                    ->label('تحديد كمقروءة')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (ContactMessage $record) => !$record->is_read)
                    ->action(function (ContactMessage $record) {
                        $record->update(['is_read' => true, 'read_at' => now()]);
                        Notification::make()->success()->title('تم تحديث الحالة.')->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListContactMessages::route('/')];
    }
}
