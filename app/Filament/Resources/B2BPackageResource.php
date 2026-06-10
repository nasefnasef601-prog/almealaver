<?php

namespace App\Filament\Resources;

use App\Filament\Resources\B2BPackageResource\Pages;
use App\Models\B2BPackage;
use App\Models\Course;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class B2BPackageResource extends Resource
{
    protected static ?string $model = B2BPackage::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'باقات المدارس';

    protected static ?string $pluralLabel = 'باقات المدارس';

    protected static ?string $label = 'باقة مدرسة';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')->label('اسم الباقة')->required(),
            Forms\Components\Select::make('school_id')->label('المدرسة')->relationship('school', 'name_ar')->searchable()->preload(),
            Forms\Components\Select::make('assigned_teacher_id')
                ->label('المعلم المسؤول')
                ->relationship('assignedTeacher', 'name')
                ->searchable()
                ->preload(),
            Forms\Components\Select::make('course_ids')
                ->label('الدورات')
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
            Forms\Components\CheckboxList::make('content_types')
                ->label('أنواع المحتوى')
                ->options([
                    'courses' => 'الدورات',
                    'foundation' => 'التأسيس',
                    'banks' => 'بنوك الأسئلة',
                    'tests' => 'الاختبارات',
                    'mockExams' => 'المحاكيات',
                    'library' => 'المكتبة',
                    'all' => 'كل المحتوى',
                ])
                ->columns(2)
                ->default(['courses']),
            Forms\Components\Select::make('type')
                ->label('نوع الباقة')
                ->options([
                    'free_access' => 'وصول مجاني',
                    'discounted' => 'خصم',
                ])
                ->default('free_access')
                ->required(),
            Forms\Components\TextInput::make('discount_percentage')->label('نسبة الخصم')->numeric()->minValue(0)->maxValue(100),
            Forms\Components\TextInput::make('max_students')->label('أقصى عدد طلاب')->numeric()->default(0),
            Forms\Components\TextInput::make('revenue_share_percentage')->label('نسبة مشاركة الإيراد')->numeric()->minValue(0)->maxValue(100),
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'active' => 'نشطة',
                    'expired' => 'منتهية',
                    'paused' => 'متوقفة',
                ])
                ->default('active')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الباقة')->searchable(),
                Tables\Columns\TextColumn::make('school.name_ar')->label('المدرسة')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('النوع')->badge(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
                Tables\Columns\TextColumn::make('access_codes_count')->counts('accessCodes')->label('الأكواد'),
                Tables\Columns\TextColumn::make('max_students')->label('حد الطلاب'),
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
            'index' => Pages\ListB2BPackages::route('/'),
        ];
    }
}
