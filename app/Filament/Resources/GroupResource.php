<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Models\Group;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'المجموعات';

    protected static ?string $pluralLabel = 'المجموعات';

    protected static ?string $label = 'مجموعة';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('النوع')
                    ->options([
                        'school' => 'مدرسة',
                        'class' => 'فصل',
                        'private_group' => 'مجموعة خاصة',
                    ])
                    ->required()
                    ->default('class'),
                Forms\Components\Select::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar'),
                Forms\Components\Select::make('parent_id')
                    ->label('المجموعة الأم')
                    ->relationship('parent', 'name'),
                Forms\Components\Select::make('owner_id')
                    ->label('المالك')
                    ->relationship('owner', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'school' => 'مدرسة',
                        'class' => 'فصل',
                        'private_group' => 'مجموعة خاصة',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('school.name_ar')
                    ->label('المدرسة'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('الأعضاء')
                    ->counts('users'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'school' => 'مدرسة',
                        'class' => 'فصل',
                        'private_group' => 'مجموعة خاصة',
                    ]),
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('المدرسة')
                    ->relationship('school', 'name_ar'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
