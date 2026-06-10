<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Models\School;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'المدارس';

    protected static ?string $pluralLabel = 'المدارس';

    protected static ?string $label = 'مدرسة';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name_ar')
                ->label('الاسم العربي')
                ->required(),
            Forms\Components\TextInput::make('name')
                ->label('الاسم الإنجليزي'),
            Forms\Components\TextInput::make('code')
                ->label('كود المدرسة')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('phone')
                ->label('الجوال'),
            Forms\Components\TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email(),
            Forms\Components\Textarea::make('address')
                ->label('العنوان')
                ->rows(2),
            Forms\Components\Toggle::make('is_active')
                ->label('نشطة')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('المدرسة')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('الطلاب')
                    ->counts('students'),
                Tables\Columns\TextColumn::make('teachers_count')
                    ->label('المعلمون')
                    ->counts('teachers'),
                Tables\Columns\TextColumn::make('supervisors_count')
                    ->label('المشرفون')
                    ->counts('supervisors'),
                Tables\Columns\TextColumn::make('classes_count')
                    ->label('الفصول')
                    ->counts('classes'),
                Tables\Columns\TextColumn::make('b2b_packages_count')
                    ->label('الباقات')
                    ->counts('b2bPackages'),
                Tables\Columns\TextColumn::make('access_codes_count')
                    ->label('الأكواد')
                    ->counts('accessCodes'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make()->label('تعديل'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
