<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LibraryItemResource\Pages;
use App\Models\LibraryItem;
use App\Models\Skill;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LibraryItemResource extends Resource
{
    protected static ?string $model = LibraryItem::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?string $navigationLabel = 'المكتبة';

    protected static ?string $pluralLabel = 'ملفات المكتبة';

    protected static ?string $label = 'ملف مكتبة';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('title')
                ->label('العنوان')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('type')
                ->label('نوع الملف')
                ->options([
                    'pdf' => 'PDF',
                    'doc' => 'مستند',
                    'video' => 'فيديو',
                ])
                ->default('pdf')
                ->required(),
            Forms\Components\TextInput::make('url')
                ->label('رابط الملف')
                ->url()
                ->maxLength(2048),
            Forms\Components\TextInput::make('size')
                ->label('الحجم'),
            Forms\Components\TextInput::make('downloads')
                ->label('عدد التحميلات')
                ->numeric()
                ->default(0),
            Forms\Components\Select::make('path_id')
                ->label('المسار')
                ->relationship('path', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('subject_id')
                ->label('المادة')
                ->relationship('subject', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('section_id')
                ->label('القسم')
                ->relationship('section', 'name_ar')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('skill_ids')
                ->label('المهارات المرتبطة')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn () => Skill::query()
                    ->where('is_active', true)
                    ->limit(250)
                    ->get()
                    ->mapWithKeys(fn (Skill $skill) => [$skill->id => $skill->name_ar ?: $skill->name])
                    ->all()),
            Forms\Components\Toggle::make('show_on_platform')
                ->label('ظاهر على المنصة')
                ->default(true),
            Forms\Components\Toggle::make('is_locked')
                ->label('مغلق لغير المشتركين')
                ->default(false),
            Forms\Components\Select::make('approval_status')
                ->label('حالة الاعتماد')
                ->options([
                    'draft' => 'مسودة',
                    'pending_review' => 'بانتظار المراجعة',
                    'approved' => 'معتمد',
                    'rejected' => 'مرفوض',
                ])
                ->default('draft')
                ->required(),
            Forms\Components\Select::make('owner_type')
                ->label('المالك')
                ->options([
                    'platform' => 'المنصة',
                    'teacher' => 'معلم',
                    'school' => 'مدرسة',
                ])
                ->default('platform')
                ->required(),
            Forms\Components\TextInput::make('owner_id')
                ->label('معرف المالك'),
            Forms\Components\Select::make('assigned_teacher_id')
                ->label('المعلم المسؤول')
                ->relationship('assignedTeacher', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Textarea::make('reviewer_notes')
                ->label('ملاحظات المراجعة')
                ->rows(3),
            Forms\Components\TextInput::make('revenue_share_percentage')
                ->label('نسبة المشاركة %')
                ->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable()->limit(45),
                Tables\Columns\TextColumn::make('type')->label('النوع')->badge(),
                Tables\Columns\TextColumn::make('path.name_ar')->label('المسار')->sortable(),
                Tables\Columns\TextColumn::make('subject.name_ar')->label('المادة')->sortable(),
                Tables\Columns\TextColumn::make('approval_status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending_review' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('show_on_platform')->label('ظاهر')->boolean(),
                Tables\Columns\IconColumn::make('is_locked')->label('مغلق')->boolean(),
                Tables\Columns\TextColumn::make('downloads')->label('تحميلات')->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('الرابط')
                    ->formatStateUsing(fn (?string $state) => $state ? Str::limit($state, 36) : '-')
                    ->copyable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['pdf' => 'PDF', 'doc' => 'مستند', 'video' => 'فيديو']),
                Tables\Filters\SelectFilter::make('approval_status')
                    ->options([
                        'draft' => 'مسودة',
                        'pending_review' => 'بانتظار المراجعة',
                        'approved' => 'معتمد',
                        'rejected' => 'مرفوض',
                    ]),
                Tables\Filters\TernaryFilter::make('show_on_platform'),
                Tables\Filters\TernaryFilter::make('is_locked'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLibraryItems::route('/'),
        ];
    }
}
