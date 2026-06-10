<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource\Pages;
use App\Models\Course;
use App\Models\DiscountCode;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'أكواد الخصم';

    protected static ?string $pluralLabel = 'أكواد الخصم';

    protected static ?string $label = 'كود خصم';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('code')
                ->label('الكود')
                ->default(fn () => strtoupper(Str::random(8)))
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('label')->label('الوصف'),
            Forms\Components\Select::make('type')
                ->label('النوع')
                ->options([
                    'percentage' => 'نسبة مئوية',
                    'fixed' => 'مبلغ ثابت',
                ])
                ->default('percentage')
                ->required(),
            Forms\Components\TextInput::make('value')->label('القيمة')->numeric()->required(),
            Forms\Components\TextInput::make('min_amount')->label('أقل مبلغ')->numeric()->default(0),
            Forms\Components\TextInput::make('max_redemptions')->label('أقصى استخدام')->numeric()->default(0),
            Forms\Components\TextInput::make('current_redemptions')->label('الاستخدام الحالي')->numeric()->default(0),
            Forms\Components\Select::make('course_ids')
                ->label('دورات محددة')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn () => Course::query()
                    ->where('is_published', true)
                    ->limit(250)
                    ->get()
                    ->mapWithKeys(fn (Course $course) => [
                        $course->id => Str::limit($course->title_ar ?: $course->title, 90),
                    ])
                    ->all()),
            Forms\Components\DateTimePicker::make('starts_at')->label('يبدأ في'),
            Forms\Components\DateTimePicker::make('expires_at')->label('ينتهي في'),
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'active' => 'نشط',
                    'paused' => 'متوقف',
                    'expired' => 'منتهي',
                ])
                ->default('active')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('الكود')->copyable()->searchable(),
                Tables\Columns\TextColumn::make('label')->label('الوصف')->limit(30),
                Tables\Columns\TextColumn::make('type')->label('النوع')->badge(),
                Tables\Columns\TextColumn::make('value')->label('القيمة'),
                Tables\Columns\TextColumn::make('current_redemptions')->label('الاستخدام'),
                Tables\Columns\TextColumn::make('max_redemptions')->label('الحد'),
                Tables\Columns\TextColumn::make('expires_at')->label('ينتهي')->dateTime(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscountCodes::route('/'),
        ];
    }
}
