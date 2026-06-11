<?php

namespace App\Filament\Resources\PaymentSettingResource;

use App\Filament\Resources\PaymentSettingResource\Pages;
use App\Models\PaymentSetting;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentSettingResource extends Resource
{
    protected static ?string $model = PaymentSetting::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'إعدادات الدفع';

    protected static ?string $pluralLabel = 'إعدادات الدفع';

    protected static ?string $label = 'إعدادات الدفع';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('payment_method')
                    ->label('طريقة الدفع')
                    ->required()
                    ->disabled(),
                Forms\Components\Toggle::make('is_active')
                    ->label('مفعلة')
                    ->default(true),
                Forms\Components\KeyValue::make('config')
                    ->label('الإعدادات'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعلة')
                    ->boolean(),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentSettings::route('/'),
            'edit' => Pages\EditPaymentSetting::route('/{record}/edit'),
        ];
    }
}
