<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class ActivityLogs extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $title = 'سجل الأنشطة';

    protected static ?string $navigationLabel = 'سجل الأنشطة';

    protected string $view = 'filament.pages.activity-logs';

    public function table(Table $table): Table
    {
        return $table
            ->query(ActivityLog::with('user')->latest('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('الإجراء')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'payment.approved' => 'قبول دفع',
                        'payment.rejected' => 'رفض دفع',
                        'review.approved' => 'اعتماد تقييم',
                        'review.unapproved' => 'إلغاء تقييم',
                        'user.created' => 'إنشاء مستخدم',
                        'user.updated' => 'تعديل مستخدم',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'payment.approved' => 'success',
                        'payment.rejected' => 'danger',
                        'review.approved' => 'success',
                        'review.unapproved' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(60),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('الإجراء')
                    ->options([
                        'payment.approved' => 'قبول دفع',
                        'payment.rejected' => 'رفض دفع',
                        'review.approved' => 'اعتماد تقييم',
                        'review.unapproved' => 'إلغاء تقييم',
                    ]),
            ])
            ->striped();
    }
}
