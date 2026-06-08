<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseReviewResource\Pages;
use App\Models\ActivityLog;
use App\Models\CourseReview;
use BackedEnum;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Table;

class CourseReviewResource extends Resource
{
    protected static ?string $model = CourseReview::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'التقييمات';

    protected static ?string $pluralLabel = 'التقييمات';

    protected static ?string $label = 'تقييم';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->label('الطالب')
                    ->disabled(),
                Forms\Components\TextInput::make('course.title_ar')
                    ->label('الدورة')
                    ->disabled(),
                Forms\Components\Select::make('rating')
                    ->label('التقييم')
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
                    ->disabled(),
                Forms\Components\Textarea::make('review')
                    ->label('المراجعة')
                    ->disabled(),
                Forms\Components\Toggle::make('is_approved')
                    ->label('موافقة'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الطالب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.title_ar')
                    ->label('الدورة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)),
                Tables\Columns\TextColumn::make('review')
                    ->label('المراجعة')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('موافق')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([1 => '1 ⭐', 2 => '2 ⭐⭐', 3 => '3 ⭐⭐⭐', 4 => '4 ⭐⭐⭐⭐', 5 => '5 ⭐⭐⭐⭐⭐']),
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('معتمد'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CourseReview $record) => !$record->is_approved)
                    ->action(function (CourseReview $record) {
                        $record->update(['is_approved' => true]);
                        ActivityLog::log('review.approved', "تم اعتماد تقييم #{$record->id}", CourseReview::class, $record->id);
                        Notification::make()->success()->title('تم اعتماد التقييم.')->send();
                    }),
                Action::make('reject')
                    ->label('إلغاء الاعتماد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CourseReview $record) => $record->is_approved)
                    ->action(function (CourseReview $record) {
                        $record->update(['is_approved' => false]);
                        ActivityLog::log('review.unapproved', "تم إلغاء اعتماد تقييم #{$record->id}", CourseReview::class, $record->id);
                        Notification::make()->warning()->title('تم إلغاء اعتماد التقييم.')->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseReviews::route('/'),
        ];
    }
}
