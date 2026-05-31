<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'الإشعارات';

    protected static ?string $pluralLabel = 'الإشعارات';

    protected static ?string $label = 'إشعار';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'payment' => 'دفع',
                        'quiz' => 'اختبار',
                        'course' => 'كورس',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'payment' => 'success',
                        'quiz' => 'info',
                        'course' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('body_ar')
                    ->label('النص')
                    ->limit(60),
                Tables\Columns\IconColumn::make('read_at')
                    ->label('مقروء')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->state(fn ($record) => !is_null($record->read_at)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'payment' => 'دفع',
                        'quiz' => 'اختبار',
                        'course' => 'كورس',
                    ]),
                Tables\Filters\TernaryFilter::make('read_at')
                    ->label('حالة القراءة')
                    ->trueLabel('مقروء')
                    ->falseLabel('غير مقروء')
                    ->nullable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
        ];
    }
}
