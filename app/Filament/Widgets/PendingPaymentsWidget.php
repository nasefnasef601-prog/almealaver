<?php

namespace App\Filament\Widgets;

use App\Models\PaymentRequest;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingPaymentsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return PaymentRequest::whereIn('status', ['pending', 'pending_manual_review'])
            ->with('user', 'course')
            ->latest();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('طلبات الدفع المعلقة')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.title_ar')
                    ->label('الكورس')
                    ->limit(25),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'pending_manual_review' ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d'),
            ])
            ->actions([
                Action::make('review')
                    ->label('مراجعة')
                    ->icon('heroicon-o-eye')
                    ->url(fn(PaymentRequest $record): string => route('filament.admin.resources.payment-requests.index')),
            ]);
    }
}
