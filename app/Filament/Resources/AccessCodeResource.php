<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessCodeResource\Pages;
use App\Models\AccessCode;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AccessCodeResource extends Resource
{
    protected static ?string $model = AccessCode::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'أكواد الدخول';

    protected static ?string $pluralLabel = 'أكواد الدخول';

    protected static ?string $label = 'كود دخول';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('code')
                ->label('الكود')
                ->default(fn () => strtoupper(Str::random(10)))
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\Select::make('school_id')->label('المدرسة')->relationship('school', 'name_ar')->searchable()->preload(),
            Forms\Components\Select::make('b2b_package_id')
                ->label('الباقة')
                ->relationship('package', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('max_uses')->label('أقصى استخدام')->numeric()->default(1)->required(),
            Forms\Components\TextInput::make('current_uses')->label('الاستخدام الحالي')->numeric()->default(0)->required(),
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
                Tables\Columns\TextColumn::make('package.name')->label('الباقة')->searchable(),
                Tables\Columns\TextColumn::make('school.name_ar')->label('المدرسة')->searchable(),
                Tables\Columns\TextColumn::make('current_uses')->label('الاستخدام'),
                Tables\Columns\TextColumn::make('max_uses')->label('الحد'),
                Tables\Columns\TextColumn::make('expires_at')->label('ينتهي')->dateTime(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessCodes::route('/'),
        ];
    }
}
