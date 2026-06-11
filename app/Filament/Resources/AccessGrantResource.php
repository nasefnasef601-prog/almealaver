<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessGrantResource\Pages;
use App\Models\AccessGrant;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class AccessGrantResource extends Resource
{
    protected static ?string $model = AccessGrant::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'صلاحيات الوصول';

    protected static ?string $pluralLabel = 'صلاحيات الوصول';

    protected static ?string $label = 'صلاحية وصول';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('course_id')
                    ->label('الكورس')
                    ->relationship('course', 'title_ar')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('package_id')
                    ->label('معرّف الباقة')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\Select::make('grant_type')
                    ->label('نوع المنح')
                    ->options([
                        'purchase' => 'شراء',
                        'admin' => 'منح إداري',
                        'promo' => 'ترويجي',
                        'trial' => 'تجريبي',
                    ])
                    ->required()
                    ->default('admin'),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'revoked' => 'ملغي',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('تاريخ البداية')
                    ->required()
                    ->default(now()),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->default(now()->addYear()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.title_ar')
                    ->label('الكورس')
                    ->searchable(),
                Tables\Columns\TextColumn::make('package_id')
                    ->label('الباقة')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('grant_type')
                    ->label('نوع المنح'),
                Tables\Columns\TextColumn::make('source_type')
                    ->label('المصدر')
                    ->badge()
                    ->placeholder('admin_manual')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'revoked' => 'ملغي',
                        'cancelled' => 'ملغي',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'revoked' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('تاريخ البداية')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('idempotency_key')
                    ->label('مفتاح التكرار')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'revoked' => 'ملغي',
                    ]),
                Tables\Filters\SelectFilter::make('source_type')
                    ->label('المصدر')
                    ->options([
                        'admin_manual' => 'إداري يدوي',
                        'payment_request' => 'طلب دفع',
                        'payment_webhook' => 'Webhook دفع',
                        'access_code' => 'كود وصول',
                        'membership' => 'اشتراك',
                    ]),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessGrants::route('/'),
            'create' => Pages\CreateAccessGrant::route('/create'),
            'edit' => Pages\EditAccessGrant::route('/{record}/edit'),
        ];
    }
}
