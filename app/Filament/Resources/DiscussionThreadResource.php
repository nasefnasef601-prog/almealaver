<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscussionThreadResource\Pages;
use App\Models\ActivityLog;
use App\Models\DiscussionThread;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class DiscussionThreadResource extends Resource
{
    protected static ?string $model = DiscussionThread::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'نقاشات الدورات';

    protected static ?string $pluralLabel = 'نقاشات الدورات';

    protected static ?string $label = 'نقاش';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('course.title_ar')
                ->label('الدورة')
                ->disabled(),
            Forms\Components\TextInput::make('author.name')
                ->label('الكاتب')
                ->disabled(),
            Forms\Components\TextInput::make('title')
                ->label('العنوان')
                ->disabled(),
            Forms\Components\Textarea::make('body')
                ->label('النص')
                ->rows(6)
                ->disabled(),
            Forms\Components\Toggle::make('is_pinned')
                ->label('مثبت'),
            Forms\Components\Toggle::make('is_resolved')
                ->label('تم الحل'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(45),
                Tables\Columns\TextColumn::make('course.title_ar')
                    ->label('الدورة')
                    ->searchable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('الكاتب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('replies_count')
                    ->label('الردود')
                    ->sortable(),
                Tables\Columns\TextColumn::make('upvotes_count')
                    ->label('التصويت')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_pinned')
                    ->label('مثبت')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_resolved')
                    ->label('تم الحل')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_pinned')
                    ->label('مثبت'),
                Tables\Filters\TernaryFilter::make('is_resolved')
                    ->label('تم الحل'),
                Tables\Filters\SelectFilter::make('course_id')
                    ->label('الدورة')
                    ->relationship('course', 'title_ar')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('pin')
                    ->label('تثبيت')
                    ->icon('heroicon-o-bookmark')
                    ->color('warning')
                    ->visible(fn (DiscussionThread $record) => ! $record->is_pinned)
                    ->action(function (DiscussionThread $record) {
                        $record->update(['is_pinned' => true]);
                        ActivityLog::log('discussion.pinned', "تم تثبيت نقاش #{$record->id}", DiscussionThread::class, $record->id);
                        Notification::make()->success()->title('تم تثبيت النقاش.')->send();
                    }),
                Action::make('unpin')
                    ->label('إلغاء التثبيت')
                    ->icon('heroicon-o-bookmark-slash')
                    ->color('gray')
                    ->visible(fn (DiscussionThread $record) => $record->is_pinned)
                    ->action(function (DiscussionThread $record) {
                        $record->update(['is_pinned' => false]);
                        ActivityLog::log('discussion.unpinned', "تم إلغاء تثبيت نقاش #{$record->id}", DiscussionThread::class, $record->id);
                        Notification::make()->success()->title('تم إلغاء التثبيت.')->send();
                    }),
                Action::make('resolve')
                    ->label('تعليم كمحلول')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (DiscussionThread $record) => ! $record->is_resolved)
                    ->action(function (DiscussionThread $record) {
                        $record->update(['is_resolved' => true]);
                        ActivityLog::log('discussion.resolved', "تم حل نقاش #{$record->id}", DiscussionThread::class, $record->id);
                        Notification::make()->success()->title('تم تعليم النقاش كمحلول.')->send();
                    }),
                Action::make('reopen')
                    ->label('إعادة فتح')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (DiscussionThread $record) => $record->is_resolved)
                    ->action(function (DiscussionThread $record) {
                        $record->update(['is_resolved' => false]);
                        ActivityLog::log('discussion.reopened', "تمت إعادة فتح نقاش #{$record->id}", DiscussionThread::class, $record->id);
                        Notification::make()->success()->title('تمت إعادة فتح النقاش.')->send();
                    }),
                Action::make('adminReply')
                    ->label('رد إداري')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->color('primary')
                    ->form([
                        Forms\Components\Textarea::make('body')
                            ->label('الرد')
                            ->required()
                            ->minLength(2)
                            ->maxLength(4000)
                            ->rows(4),
                    ])
                    ->action(function (array $data, DiscussionThread $record) {
                        DB::transaction(function () use ($record, $data) {
                            $record->replies()->create([
                                'author_id' => auth()->id(),
                                'body' => trim((string) $data['body']),
                                'is_instructor_reply' => true,
                            ]);

                            $record->increment('replies_count');
                            $record->touch();
                        });

                        ActivityLog::log('discussion.admin_reply', "تمت إضافة رد إداري على نقاش #{$record->id}", DiscussionThread::class, $record->id);
                        Notification::make()->success()->title('تم إرسال الرد الإداري.')->send();
                    }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscussionThreads::route('/'),
        ];
    }
}
