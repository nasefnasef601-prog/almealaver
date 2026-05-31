<?php

namespace App\Filament\Widgets;

use App\Models\PaymentRequest;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestActivityWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::latest()->take(5)
            )
            ->heading('آخر المستخدمين المسجلين')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('الدور')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'teacher' => 'warning',
                        'supervisor' => 'info',
                        'parent' => 'gray',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ]);
    }
}
